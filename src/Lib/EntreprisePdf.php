<?php
namespace App\Lib;

defined('APP_RUNNING') or exit('Acces direct interdit.');

/**
 * Generation d'un PDF (tableau des stages) sans dependance externe.
 *
 * Produit un PDF minimal mais valide (police Helvetica, encodage WinAnsi),
 * en mode paysage A4. Le tableau reprend exactement les stages passes en
 * parametre (donc les filtres deja appliques a l'ecran).
 */
class EntreprisePdf
{
    // A4 paysage, en points.
    private const W = 842;
    private const H = 595;
    private const MARGIN = 28;

    /** Colonnes : [libelle, largeur, cle de donnee]. */
    private const COLS = [
        ['Entreprise', 200, 'nom'],
        ['CP', 80, 'code_postal'],
        ['Adresse', 250, 'adresse'],
        ['Ville', 145, 'ville'],
        ['Stagiaires', 100, 'nb_stages']
    ];

    /** @var string[] Flux de contenu de chaque page. */
    private array $pages = [];
    private string $buf = '';
    private float $y = 0;

    /** Point d'entree : envoie le PDF au navigateur (telechargement). */
    public static function download(array $entreprises, string $filters = ""): void
    {
        $pdf = new self();
        $bytes = $pdf->build($entreprises, $filters);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="entreprises.pdf"');
        header('Content-Length: ' . strlen($bytes));
        echo $bytes;
        exit;
    }

    private function build(array $entreprises, string $filters): string
    {
        $this->newPage($filters);
        foreach ($entreprises as $s) {
            if ($this->y < self::MARGIN + 16) {
                $this->endPage();
                $this->newPage($filters);
            }
            $this->row($s);
        }
        $this->endPage();
        return $this->assemble();
    }

    /** Demarre une nouvelle page avec titre + en-tete de tableau. */
    private function newPage(string $filters): void
    {
        $this->buf = '';
        $this->y = self::H - self::MARGIN;

        // Titre
        $this->text(self::MARGIN, $this->y, 14, 'Liste des Entreprises - Lycee Jean Rostand');
        $this->y -= 16;
        $sub = $this->filterSummary($filters);
        if ($sub !== '') {
            $this->text(self::MARGIN, $this->y, 9, $sub);
        }
        $this->text(self::W - self::MARGIN - 120, $this->y, 9, 'Genere le ' . date('d/m/Y'));
        $this->y -= 18;

        // En-tete du tableau
        $this->headerRow();
    }

    private function headerRow(): void
    {
        $x = self::MARGIN;
        $top = $this->y;
        foreach (self::COLS as [$label, $w]) {
            $this->text($x + 2, $this->y - 10, 8, $label, true);
            $x += $w;
        }
        $this->y -= 13;
        $this->line(self::MARGIN, $this->y + 1, self::W - self::MARGIN, $this->y + 1);
        $this->line(self::MARGIN, $top + 2, self::W - self::MARGIN, $top + 2);
    }

    private function row(array $s): void
    {
        $x = self::MARGIN;
        foreach (self::COLS as [$label, $w, $key]) {
            $val = (string) ($s[$key] ?? '');
            $this->text($x + 2, $this->y - 8, 9, $val);
            $x += $w;
        }
        $this->y -= 12;
        $this->line(self::MARGIN, $this->y + 1, self::W - self::MARGIN, $this->y + 1, 0.85);
    }

    private function endPage(): void
    {
        $this->pages[] = $this->buf;
    }

    /* ---------- Primitives de dessin (operateurs PDF) ---------- */

    private function text(float $x, float $y, int $size, string $s, bool $bold = false, ?array $rgb = null): void
    {
        $font = $bold ? '/F2' : '/F1';
        $color = $rgb ? sprintf('%.2f %.2f %.2f rg', $rgb[0], $rgb[1], $rgb[2]) : '0 0 0 rg';
        $this->buf .= sprintf("%s BT %s %d Tf %.2f %.2f Td (%s) Tj ET\n",
            $color, $font, $size, $x, $y, $this->esc($this->enc($s)));
    }

    private function line(float $x1, float $y1, float $x2, float $y2, float $gray = 0.4): void
    {
        $this->buf .= sprintf("%.2f G 0.5 w %.2f %.2f m %.2f %.2f l S\n", $gray, $x1, $y1, $x2, $y2);
    }

    /* ---------- Utilitaires ---------- */

    /** Convertit l'UTF-8 en Windows-1252 (police standard Helvetica). */
    private function enc(string $s): string
    {
        $out = @iconv('UTF-8', 'CP1252//TRANSLIT//IGNORE', $s);
        return $out !== false ? $out : $s;
    }

    /** Echappe les caracteres speciaux d'une chaine PDF. */
    private function esc(string $s): string
    {
        return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '', ' '], $s);
    }

    /** Tronque un texte pour qu'il tienne dans une largeur de colonne. */
    private function fit(string $s, float $w): string
    {
        $s = $this->enc($s);
        $max = (int) floor(($w - 4) / (8 * 0.5));
        if (strlen($s) <= $max) {
            return $this->decodeBack($s);
        }
        return $this->decodeBack(substr($s, 0, max(0, $max - 3)) . '...');
    }

    /** Re-encode en UTF-8 pour que text()/enc() retravaille proprement. */
    private function decodeBack(string $s): string
    {
        $out = @iconv('CP1252', 'UTF-8//IGNORE', $s);
        return $out !== false ? $out : $s;
    }

    /** Resume lisible des filtres actifs pour le sous-titre. */
    private function filterSummary(string $f): string
    {
        $labels = [
            'q' => 'Recherche'
        ];
        
        // On initialise $parts à vide
        $parts = ''; 
        if (!empty($f)) {
            // On utilise la bonne clé 'q' (ou on adapte selon ce que tu reçois)
            $parts = ($labels['q'] ?? 'Filtre') . ' : ' . $f;
        }
        
        return $parts !== '' ? 'Filtre -> ' . $parts : 'Toute(s) les entreprise(s)';
    }

    /* ---------- Assemblage du fichier PDF ---------- */

    private function assemble(): string
    {
        $objects = [];
        // 1 = Catalog, 2 = Pages, 3 = Helvetica, 4 = Helvetica-Bold
        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';

        $pageObjNums = [];
        $nextObj = 5;
        foreach ($this->pages as $content) {
            $contentObj = $nextObj++;
            $pageObj = $nextObj++;
            $objects[$contentObj] = "<< /Length " . strlen($content) . " >>\nstream\n" . $content . "endstream";
            $objects[$pageObj] = sprintf(
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %d %d] '
                . '/Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents %d 0 R >>',
                self::W, self::H, $contentObj
            );
            $pageObjNums[] = $pageObj;
        }

        $kids = implode(' ', array_map(fn($n) => "$n 0 R", $pageObjNums));
        $objects[2] = '<< /Type /Pages /Kids [' . $kids . '] /Count ' . count($pageObjNums) . ' >>';

        // Ecriture avec table de references croisees (xref).
        ksort($objects);
        $maxId = max(array_keys($objects));
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        for ($i = 1; $i <= $maxId; $i++) {
            if (!isset($objects[$i])) {
                continue;
            }
            $offsets[$i] = strlen($pdf);
            $pdf .= $i . " 0 obj\n" . $objects[$i] . "\nendobj\n";
        }
        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 " . ($maxId + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= $maxId; $i++) {
            if (isset($offsets[$i])) {
                $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
            } else {
                $pdf .= "0000000000 65535 f \n";
            }
        }
        $pdf .= "trailer\n<< /Size " . ($maxId + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefPos . "\n%%EOF";
        return $pdf;
    }
}

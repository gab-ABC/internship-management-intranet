// Petits comportements cote client.

// --- Afficher / masquer les mots de passe ---
// Toute case a cocher portant la classe "js-show-password" bascule les champs
// mot de passe (classe "js-password") situes dans le meme formulaire.
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-show-password').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            var form = checkbox.closest('form');
            if (!form) {
                return;
            }
            var type = checkbox.checked ? 'text' : 'password';
            form.querySelectorAll('.js-password').forEach(function (input) {
                input.type = type;
            });
        });
    });
});

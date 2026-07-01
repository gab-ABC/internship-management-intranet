document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('select-entreprise');
    const bloc   = document.getElementById('bloc-nouvelle-entreprise');

    const select_prof = document.getElementById('select-professeur');
    const bloc_prof   = document.getElementById('bloc-nouveau-professeur');

    function toggle(select, bloc) {
        if (select.value === '') {
            bloc.classList.remove('d-none');
        } else {
            bloc.classList.add('d-none');
        }
    }

    select.addEventListener('change', () => toggle(select, bloc));
    select_prof.addEventListener('change', () => toggle(select_prof, bloc_prof));

    toggle(select, bloc);
    toggle(select_prof, bloc_prof);
});
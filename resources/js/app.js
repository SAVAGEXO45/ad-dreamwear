document.querySelectorAll('[data-gallery]').forEach((gallery) => {
    const mainImage = gallery.querySelector('[data-gallery-main]');
    const buttons = gallery.querySelectorAll('button[data-photo]');
    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            mainImage.src = button.dataset.photo;
            mainImage.alt = button.dataset.alt;
            buttons.forEach((item) => item.setAttribute('aria-pressed', String(item === button)));
        });
    });
});

document.querySelectorAll('.mobile-nav a').forEach((link) => {
    link.addEventListener('click', () => link.closest('details').removeAttribute('open'));
});

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});

const dialog = document.querySelector('[data-request-dialog]');

document.querySelectorAll('[data-open-request]').forEach((button) => {
    button.addEventListener('click', () => dialog?.showModal());
});

document.querySelector('[data-close-request]')?.addEventListener('click', () => dialog?.close());
dialog?.addEventListener('click', (event) => {
    if (event.target === dialog) dialog.close();
});

function requestCurrentLocation(button, form, latitudeField, longitudeField, feedback, submit = false) {
    const latitude = form.querySelector(latitudeField);
    const longitude = form.querySelector(longitudeField);
    const message = form.querySelector(feedback);

    if (!navigator.geolocation) {
        form.dataset.locationReady = 'false';
        message.textContent = 'Location is not available in this browser. Try a supported browser.';
        return;
    }

    button.disabled = true;
    button.dataset.originalText ??= button.textContent;
    button.textContent = 'Finding your location…';
    message.textContent = '';
    navigator.geolocation.getCurrentPosition(
        ({ coords }) => {
            latitude.value = coords.latitude.toFixed(5);
            longitude.value = coords.longitude.toFixed(5);
            if (submit) {
                form.requestSubmit();
                return;
            }
            button.disabled = false;
            button.textContent = button.dataset.originalText;
            form.requestSubmit();
        },
        () => {
            button.disabled = false;
            button.textContent = button.dataset.originalText;
            form.dataset.locationReady = 'false';
            message.textContent = 'We couldn’t get your location. Check your browser permission and try again.';
        },
        { enableHighAccuracy: false, timeout: 10000, maximumAge: 300000 },
    );
}

const locationForm = document.querySelector('[data-location-form]');
locationForm?.addEventListener('submit', (event) => {
    const button = locationForm.querySelector('[data-locate]');
    if (locationForm.dataset.locationReady === 'true') return;
    event.preventDefault();
    requestCurrentLocation(
        button,
        locationForm,
        '[data-search-latitude]',
        '[data-search-longitude]',
        '[data-location-feedback]',
    );
    locationForm.dataset.locationReady = 'true';
});

document.querySelector('[data-trigger-location]')?.addEventListener('click', () => {
    const form = document.querySelector('[data-location-form]');
    const button = form.querySelector('[data-locate]');
    form.dataset.locationReady = 'true';
    form.scrollIntoView({ behavior: 'smooth', block: 'center' });
    requestCurrentLocation(
        button,
        form,
        '[data-search-latitude]',
        '[data-search-longitude]',
        '[data-location-feedback]',
        true,
    );
});

document.querySelector('[data-request-form]')?.addEventListener('submit', (event) => {
    const form = event.currentTarget;
    if (form.dataset.locationReady === 'true') return;
    event.preventDefault();
    form.dataset.locationReady = 'true';
    const button = form.querySelector('[type="submit"]');
    requestCurrentLocation(
        button,
        form,
        '[data-request-latitude]',
        '[data-request-longitude]',
        '[data-request-feedback]',
        true,
    );
});

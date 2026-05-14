import 'bootstrap';

function syncProgramItemContentFields(form) {
    const contentType = form.querySelector('[name="content_type"]')?.value;

    form.querySelectorAll('[data-content-field]').forEach((field) => {
        const isActive = field.dataset.contentField === contentType;

        field.classList.toggle('d-none', ! isActive);
        field.querySelectorAll('input, textarea, select').forEach((input) => {
            input.disabled = ! isActive;
        });
    });
}

function syncProgramItemScheduleFields(form) {
    const recurrenceType = form.querySelector('[name="recurrence_type"]')?.value;

    form.querySelectorAll('[data-schedule-field]').forEach((field) => {
        const isActive = field.dataset.scheduleField === recurrenceType;

        field.classList.toggle('d-none', ! isActive);
        field.querySelectorAll('input, textarea, select').forEach((input) => {
            input.disabled = ! isActive;
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-program-item-form]').forEach((form) => {
        syncProgramItemContentFields(form);
        syncProgramItemScheduleFields(form);

        form.querySelector('[name="content_type"]')?.addEventListener('change', () => {
            syncProgramItemContentFields(form);
        });

        form.querySelector('[name="recurrence_type"]')?.addEventListener('change', () => {
            syncProgramItemScheduleFields(form);
        });
    });
});

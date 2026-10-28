document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-local-dates]').forEach(form => {
        const zone = form.querySelector('[name="timezone"]');
        const hint = form.querySelector('[data-zone-hint]');
        if (zone && !zone.value) zone.value = Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';
        if (hint && zone) hint.textContent = `Date/time zone: ${zone.value || 'UTC'}`;
    });
});

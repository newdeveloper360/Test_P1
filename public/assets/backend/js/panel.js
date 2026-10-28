document.addEventListener('DOMContentLoaded', () => {
    const element = document.getElementById('actionConfirmation');
    if (!element || !window.bootstrap) return;
    const modal = new bootstrap.Modal(element);
    let pending, submitter;
    document.addEventListener('submit', event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.dataset.confirm && form.dataset.confirmed !== '1') {
            event.preventDefault();
            pending = form; submitter = event.submitter;
            element.querySelector('[data-confirm-message]').textContent = form.dataset.confirm;
            const destructive = form.querySelector('input[name="_method"][value="DELETE"]');
            element.querySelector('[data-confirm-title]').textContent = destructive ? 'Confirm deletion' : 'Confirm action';
            element.querySelector('[data-confirm-submit]').textContent = destructive ? 'Yes, delete' : 'Confirm';
            const current = form.closest('.modal');
            if (current) {
                current.addEventListener('hidden.bs.modal', () => modal.show(), {once:true});
                bootstrap.Modal.getInstance(current)?.hide();
            } else modal.show();
            return;
        }
        delete form.dataset.confirmed;
        if (form.dataset.submitting === '1') { event.preventDefault(); return; }
        form.dataset.submitting = '1';
        const button = event.submitter;
        if (button) window.setTimeout(() => { button.disabled = true; button.setAttribute('aria-busy','true'); }, 0);
    });
    element.querySelector('[data-confirm-submit]').addEventListener('click', () => {
        if (!pending) return;
        const form = pending, button = submitter;
        pending = null; modal.hide(); form.dataset.confirmed = '1';
        form.requestSubmit(button || undefined);
    });
    window.addEventListener('pageshow', event => {
        if (!event.persisted) return;
        document.querySelectorAll('form[data-submitting]').forEach(form => {
            delete form.dataset.submitting;
            form.querySelectorAll('button[aria-busy]').forEach(button => {button.disabled=false;button.removeAttribute('aria-busy');});
        });
    });
});

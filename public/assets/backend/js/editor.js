(() => {
    const contentsCss = document.currentScript?.dataset.contentsCss;
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.CKEDITOR) return;
        document.querySelectorAll('.rich-editor').forEach(field => {
            CKEDITOR.replace(field.id, {
                contentsCss: contentsCss ? [contentsCss] : undefined,
                height: 350,
                removePlugins: 'image,flash,iframe,forms,exportpdf',
                toolbar: [
                    {name: 'document', items: ['Source']},
                    {name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'RemoveFormat']},
                    {name: 'paragraph', items: ['NumberedList', 'BulletedList', 'Blockquote']},
                    {name: 'links', items: ['Link', 'Unlink']},
                    {name: 'styles', items: ['Format']},
                    {name: 'tools', items: ['Maximize']}
                ],
                format_tags: 'p;h2;h3;h4',
                allowedContent: false
            });
        });
    });
})();

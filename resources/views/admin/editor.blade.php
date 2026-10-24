@push('scripts')
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js" defer></script>
<script>document.addEventListener('DOMContentLoaded',function(){if(window.CKEDITOR){document.querySelectorAll('.rich-editor').forEach(function(field){CKEDITOR.replace(field.id,{height:350,removePlugins:'image,flash,iframe,forms,exportpdf',toolbar:[{name:'document',items:['Source']},{name:'basicstyles',items:['Bold','Italic','Underline','Strike','RemoveFormat']},{name:'paragraph',items:['NumberedList','BulletedList','Blockquote']},{name:'links',items:['Link','Unlink']},{name:'styles',items:['Format']},{name:'tools',items:['Maximize']}],format_tags:'p;h2;h3;h4',allowedContent:false});});}});</script>
@endpush

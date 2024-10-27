jQuery(document).ready(function ($) {
    $('#amselectall').toggle(
        function() {
            $('#adscendselectpages .ampagelist').prop('checked', true);
            $('#amselectall').prop('value', 'Deselect All');
        },
        function() {
            $('#adscendselectpages .ampagelist').prop('checked', false);
            $('#amselectall').prop('value', 'Select All');
        }
    );
});
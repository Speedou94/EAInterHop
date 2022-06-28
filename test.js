$(document).ready(function () {
    const script_tag = document.getElementById('testscript');
    const search_term = script_tag.getAttribute("data-1");
    $("#provider-timezone").val(search_term);
});

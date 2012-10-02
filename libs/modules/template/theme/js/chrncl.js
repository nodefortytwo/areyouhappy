$(document).ready(function() {
    $('.typeahead').typeahead();
    $('.fileupload').fileupload();
    $('a').click(function() {
        event.preventDefault();
        window.location = $(this).attr("href");
    });

});
// When ready...
window.addEventListener("load", function() {
    // Set a timeout...
    setTimeout(function() {
        // Hide the address bar!
        window.scrollTo(0, 1);
    }, 0);
});
function getURL($path) {
    return SYSTEM.BASE_PATH + $path;
}

function userGetStories(query, process) {
    alert('test');
}

document.getElementById('commentForm').addEventListener('submit', function(event) {
    event.preventDefault();

    var post_id = document.getElementById('post_id').value;
    var user_id = document.getElementById('user_id').value;
    var content = document.getElementById('content').value;

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'comment.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (this.status == 200) {
            alert(this.responseText);
        }
    };
    xhr.send('post_id=' + encodeURIComponent(post_id) + '&user_id=' + encodeURIComponent(user_id) + '&content=' + encodeURIComponent(content));
});

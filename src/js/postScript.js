document.getElementById("postForm").addEventListener("submit", function (e) {
    e.preventDefault();
  
    var content = document.getElementById("content").value;
  
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "src/php/post.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  
    xhr.onload = function () {
      if (this.status == 200) {
        try {
          var response = JSON.parse(this.responseText);
          if (response.error) {
            console.error('Error:', response.error);
          } else {
            console.log('Publié avec succès !');
            loadPosts();  // Rafraîchir la liste de posts
          }          
        } catch (error) {
          console.error("Erreur lors du parsing du JSON:", error);
        }
      } else {
        console.error("Erreur lors de la requête. Status:", this.status);
      }
    };
    xhr.onerror = function () {
      console.error("Echec de la requête.");
    };
    
    xhr.send("content=" + encodeURIComponent(content));
});

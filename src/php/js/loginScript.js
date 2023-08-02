// Login
document.getElementById("loginForm").addEventListener("submit", function (e) {
  e.preventDefault();

  var username = document.getElementById("username").value;

  var xhr = new XMLHttpRequest();
  xhr.open("POST", "src/php/login.php", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.send("username=" + encodeURIComponent(username));
  xhr.onload = function () {
    if (this.status == 200) {
      try {
        var response = JSON.parse(this.responseText);

        var errorMessageElement = document.getElementById("errorMessage");
        var successMessageElement = document.getElementById("successMessage");
        if (response.error) {
          // Afficher le message d'erreur
          errorMessageElement.textContent = response.error;
          // Effacer le message de succès s'il y en a un
          successMessageElement.textContent = "";
        } else {
          // Effacer le message d'erreur s'il y en a un
          errorMessageElement.textContent = "";
          // Afficher le message de succès
          successMessageElement.textContent = response.success;
          // Rediriger l'utilisateur vers la page de jeu après 2 secondes
          setTimeout(function () {
            window.location.href = "src/php/dashboard.php";
          }, 1000);
        }
      } catch (error) {
        console.error("Erreur lors du parsing du JSON:", error);
      }
    }
  };
  xhr.onerror = function () {
    console.error("Request failed.");
  };
  
});




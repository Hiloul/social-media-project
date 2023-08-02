<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil Social Media</title>
    <style>
        * {box-sizing: border-box;}
        nav {
            position: fixed;
            top: 0;
            width: 100%;
            background-color: #ff69b4;
            padding: 15px;
            display: flex;
            justify-content: space-around;
        }
        nav a {
            color: #ffffff;
            text-decoration: none;
            font-size: 1.2em;
        }
        nav a:hover {
            color: #ffb6c1;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(to bottom, #ff69b4, #ffffff);
            font-family: Arial, sans-serif;
        }
        body h1 {
            text-align: center;
            color: #ff69b4;
            font-size: 2.5em;
            text-shadow: 2px 2px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: #ff69b4;
            padding: 15px;
            text-align: center;
            color: #ffffff;
            font-size: 1.2em;
        }
        @media screen and (max-width: 768px) {
            nav, footer {padding: 10px;}
            nav a, footer a {font-size: 1em;}
            body h1 {font-size: 2em;}
        }
        @media screen and (max-width: 480px) {
            nav, footer {padding: 5px;}
            nav a, footer a {font-size: 0.8em;}
            body h1 {font-size: 1.5em;}
        }
    </style>
</head>
<body>
    <!-- NavBar interactive connecté ou pas -->
    <nav>
        <a href="home.php">Accueil</a>
        <?php
        if (isset($_SESSION['username'])) {
            echo '<a href="logout.php">Déconnexion</a>';
        } else {
            echo '<a href="http://localhost/php/social-media-project/register.html">Connexion/Inscription</a>';
        }
        ?>
    </nav>

    <h1>Hello world</h1>
   
    <footer>Social Media &copy;2023</footer>
</body>

</html>
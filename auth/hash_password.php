<?php
   // Le mot de passe que vous souhaitez hacher
   $password = 'admin123'; // Remplacez par le mot de passe souhaité

   // Hachage du mot de passe
   $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

   // Afficher le mot de passe haché
   echo $hashedPassword;
   ?>
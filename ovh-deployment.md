# Projet-Blog 

Procédure pour un déploiement pour serveur (si VPS ou Dédié):

Déploiement d'une application Symfony
Prérequis
Afin de pouvoir déployer une application Symfony sur un serveur, il faut au préalable avoir accès à
un serveur, qu’il soit mutualisé, dédié ou un VPS (Virtual Private Server).
Un serveur mutualisé sur OVH (par exemple) est généralement déjà configuré avec PHP, Apache
et Mysql, Composer et Git. Sur un serveur dédié ou un VPS, ces outils seront à installer
manuellement, généralement en ligne de commandes car les serveurs sont sous Linux.
Étapes de déploiement
Préparation du Serveur (si VPS ou Dédié) :
Installation des dépendances :
- PHP : Assurez-vous que votre serveur a la bonne version de PHP pour votre projet Symfony.
  Vous pouvez vérifier les versions installées avec `php -v`.
- Composer* : Composer doit être installé pour gérer les dépendances PHP de votre projet.
- Apache : Assurez-vous qu'Apache est installé et configuré.
  Pour installer ces outils, connectez-vous à votre serveur via SSH et utilisez les commandes
  appropriées pour votre système d'exploitation (par exemple, `apt` pour Ubuntu).
  Configuration de l'environnement
  Modifiez votre fichier de configuration PHP (`php.ini`) pour vous assurer qu'il répond aux
  exigences de Symfony, comme les extensions PHP nécessaires (PDO, cURL, GD, etc.).
  Transfert de l'application
  Utilisation de Git
1. Initialisez Git dans votre projet local (sur votre ordinateur donc) si ce n'est pas déjà fait :
   git init
   git add .
   git commit -m "Initial commit"
2. Poussez le code vers un dépôt distant (GitHub)
3. Clonez le dépôt sur le serveur OVH :
   Connectez-vous au serveur via SSH et clonez votre projet dans le répertoire souhaité :
   git clone https://github.com/votre-utilisateur/votre-projet.git /chemin/vers/votre-projet
   Alternative : Transfert par FTP
   Si vous préférez utiliser FTP, vous pouvez transférer les fichiers de votre application à l'aide d'un
   client FTP (comme FileZilla).
   Installation des Dépendances
   Sur le serveur, exécutez Composer pour installer les dépendances de votre projet. Pour cela, en
   lignes de commandes :
   cd /chemin/vers/votre-projet
   composer install --no-dev --optimize-autoloader
   L'option `--no-dev` garantit que les dépendances de développement ne sont pas installées
   (comme le profiler) ce qui est recommandé pour un environnement de production.
   Configuration de l'environnement de production
   Variables d’environnement :
   Configurez les variables d'environnement nécessaires en créant un fichier `.env.local` dans le
   répertoire de votre projet sur le serveur. Assurez-vous que `APP_ENV` est défini sur `prod` et que
   `APP_DEBUG` est défini sur `0` :
   APP_ENV=prod
   APP_DEBUG=0
   DATABASE_URL=mysql://user:password@host:port/dbname
   Permissions
   Assurez-vous que les répertoires `var` et `public` ont les permissions correctes pour le serveur
   web. Cela peut inclure la modification du propriétaire et du groupe de ces répertoires. Pour cela,
   en ligne de commandes :
   chown -R www-data:www-data var public
   chmod -R 775 var public
   Configuration Apache
   Si Apache n’est pas configuré sur votre serveur : créez un fichier de configuration pour votre site
   Symfony, par exemple `/etc/apache2/sites-available/votre-site.conf` :
   <VirtualHost *:80>
   ServerName votre-domaine.com
   DocumentRoot /chemin/vers/votre-projet/public
   <Directory /chemin/vers/votre-projet/public>
   AllowOverride All
   Order Allow,Deny
   Allow from All
   Options Indexes FollowSymLinks
   Require all granted
   </Directory>
   ErrorLog ${APACHE_LOG_DIR}/error.log
   CustomLog ${APACHE_LOG_DIR}/access.log combined
   </VirtualHost>
   Activez le site et le module `rewrite` d'Apache, puis redémarrez le serveur. Pour cela en ligne de
   commandes :
   sudo a2ensite votre-site
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   Optimisations finales
   Construisez le cache Symfony pour l'environnement de production. Pour cela
   php bin/console cache:clear --env=prod --no-debug
   php bin/console cache:warmup --env=prod --no-debug
   Sécurité
   Assurez-vous que votre application est sécurisée en suivant les recommandations de Symfony,
   comme la désactivation des erreurs détaillées et la sécurisation des fichiers `.env`.
   HTTPS
   Il est fortement recommandé de configurer HTTPS sur votre serveur. Vous pouvez utiliser Let's
   Encrypt pour obtenir un certificat SSL gratuit.
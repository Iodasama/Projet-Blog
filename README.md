# Projet-Blog

Il existe plusieurs types de serveur en fonction de l’importance de l’application (et des coûts associés) : mutualisé, dédié ou un VPS (Virtual Private Server).
Nous optons pour un serveur mutualisé (cas d’O2Switch) qui est généralement configuré avec PHP, Apache, Mysql, Composer et Git.

La méthode de déploiement recommandé par Symfony est la méthode de déploiement par Git. Il existe plusieurs manières de déployer une application mais nous optons pour le déploiement suivant :
manuellement, en lançant les commandes en SSH, en clonant les données d'un dépôt GIT externe

En résumé, pour déployer une application Symfony il faut :
S’assurer que les versions de Apache, PHP, MySQL, Composer et Git sont compatibles (via symfony check:requirements qui permet de vérifier si les prérequis techniques (comme la version de PHP et les extensions nécessaires) sont respectés pour exécuter une application Symfony. Cependant pour Apache, MySQL, Composer et Git il faut vérifier manuellement ).
Se connecter en SSH au serveur.
Se placer dans le dossier qui va contenir l'application puis récupérer les fichiers de notre projet avec un git clone.
Installer les dépendances du projet avec Composer. S’il y a des erreurs, alors il est recommandé d’essayer en configurant d'abord l'application puis en installant les dépendances.
Configurer l'application en créant/modifiant le fichier .env.local et éventuellement les fichiers du dossier config. Bien s'assurer de ne pas laisser l'application en mode développement et la mettre en mode production.
Enfin si l'application a une base de données, il peut être nécessaire de créer les tables de la base et charger les  données. Quand elle est existante, nous nous assurons que les paramètres de la base de données dans .env.local sont corrects pour l'environnement de production. Puis migration de la base de données sur le serveur.


1. Préparation de l'Application en Local : nettoyage des fichiers inutiles.

2. Configuration du Serveur Mutualisé : vérification des versions des différents modules. La plupart des  logiciels qui peuvent être utilisés dans l'écosystèmes Symfony sont installés nativement chezO2Switch :
   Composer est installé nativement, en version 2. Pour installer et utiliser composer, un accès SSH est fortement conseillé. Il s’agit d’ajouter notre IP à la liste des IPs autorisées à se connecter en SSH au serveur via Autorisation SSH de O2switch.
   Php est installé nativement néanmoins il est possible de sélectionner une autre version via le sélecteur de version.
   Git est installé nativement. Si cela passe par SSH, bien penser à autoriser l'accès via l’outil de liste blanche (connexion entrante & sortante).

Transfert de l'application via github (clonage du projet dans le répertoire adéquat du serveur) :
Nous allons nous placer dans le dossier qui va contenir l'application et récupérer les données avec git clone :
git clone https://github.com/Iodasama/Projet-Blog.git

3. Gestion des dépendances : Maintenant que notre code a été déployé, il faut  installer les dépendances PHP du projet, avec l'aide de Composer. Pour cela, il suffit d’exécuter la commande « composer install » et composer se chargera de télécharger toutes les dépendances de notre projet, listées dans le fichier composer.json qui définit la liste des dépendances nécessaires au bon fonctionnement du projet.
   En complément, la ligne de commande composer install --no-dev —optimize-autoloader permet de ne pas installer les dépendances de développement (comme le profiler) ce qui est recommandé pour un environnement de production:
   Fonctionnement de --optimize-autoloader
   Par défaut, Composer génère un autoloader standard qui utilise un mécanisme de recherche pour localiser les fichiers des classes lorsque vous les chargez. Cette recherche implique de vérifier les namespaces et les chemins de fichier correspondants pour chaque classe, ce qui peut être lent, surtout lorsque vous avez de nombreuses dépendances ou un grand nombre de classes dans votre projet. Avec l'option —optimize-autoloader, Composer va pré-générer une carte de toutes les classes et de leurs chemins respectifs dans un fichier spécifique. Cela permet de réduire la nécessité de recherches dynamiques, car l'autoloader saura directement où se trouvent les fichiers des classes, ce qui permet un chargement beaucoup plus rapide en production.
4. Configuration de l'environnement de production (de l’application) : en mettant à jour le fichier .env qui se situe à la racine du projet mais comme nous l’avons vu nous avons fait une copie le fichier « .env.local », c’est lui qui contient les données de la base de données etc. et il n’est donc pas versionné. Il faudra donc le re-créer et y inscrire notamment les informations de la base de données à utiliser sur le serveur de production:
   La configuration des variables d'environnement nécessaires dans le env.local : `APP_ENV`= prod et APP_DEBUG=0  ainsi que les paramètres de la base données le DATABASE_URL et enfin le code du APP_SECRETS)




5. Permissions :
   Il faut s’assurer que les répertoires var/cache/ et var/log/ ont les permissions correctes pour que le serveur puisse lire et écrire dans ces répertoires. Cela peut inclure la modification du propriétaire et du groupe de ces répertoires pour correspondre à l'utilisateur et au groupe du serveur web (généralement www-data sur Apache).
   Pour cela, exécutez les commandes suivantes en ligne de commande :
# Modifier le propriétaire et le groupe des répertoires pour www-data
chown -R www-data:www-data var/cache var/log

# Appliquer les permissions de lecture, écriture et exécution pour l'utilisateur et le groupe
chmod -R 775 var/cache var/log

Ces commandes assurent que le serveur web peut accéder, lire et écrire dans ces répertoires. Cela est crucial pour le bon fonctionnement de l'application Symfony en production.
6. Configuration de la Base de Données : 
   Etape initiale :Comme le projet utilise les migrations Doctrine pour gérer les modifications de la structure de la base de données, il est important d'appliquer ces migrations avant d'importer les données en production.Nous pouvons  utiliser la commande suivante pour appliquer les migrations à la base de données de production :
   php bin/console doctrine:migrations:migrate --no-interaction --env=prod
   Cette commande va exécuter toutes les migrations en attente et s'assurer que la structure de la base de données est à jour avec le schéma attendu par votre application.
   Si les données en production doivent être une copie de celles en local, il faudra exporter les données de la base de données locale (ce que l’on appelle un dump). Cette étape se fait soit en ligne de commandes soit via PHPMyAdmin. Ensuite il faut importer ces données dans la base de données du site en production. Les données et la structure de la base de données seront donc re-créées sur la base de données de production :

Etape 1 :
Exporter la base de données locale (Dump)
Un "dump" de base de données est une copie complète des données et de la structure de la base de données. Il peut être réalisé de deux manières :
Via ligne de commande : Si vous utilisez MySQL/MariaDB, vous pouvez utiliser la commande mysqldump pour exporter la base de données :
mysqldump -u [username] -p [database_name] > dump.sql
Cette commande va créer un fichier dump.sql contenant l'ensemble des tables et des données de la base de données locale.
Via PHPMyAdmin : Si vous utilisez un outil comme PHPMyAdmin, vous pouvez exporter la base de données en vous connectant à votre base locale, en sélectionnant la base, puis en cliquant sur "Exporter" et en suivant les instructions.

Etape 2:
Importer les données en production
Une fois le fichier dump.sql généré, vous devez l'importer dans la base de données de production. Encore une fois, cela peut se faire de plusieurs manières :
Via ligne de commande : Sur votre serveur de production, connectez-vous à votre base de données et utilisez la commande suivante pour importer les données :
mysql -u [username] -p [database_name] < dump.sql

Via PHPMyAdmin : Si vous avez accès à PHPMyAdmin sur le serveur de production, vous pouvez utiliser l'onglet "Importer" pour charger le fichier dump.sql et restaurer les données et la structure de la base de données.

7.Optimisations finales
Construisez le cache Symfony pour l'environnement de production.
Pour cela
php bin/console cache:clear (--env=prod —no-debug)
php bin/console cache:warmup (--env=prod —no-debug)


8. Vérification et Tests : S’assurer que l’application est bien accessible et que les fonctionnalités sont correctes

HTTPS
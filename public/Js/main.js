const deleteArticleButtons = document.querySelectorAll('.js-admin-article-delete')

// je recupere tous les elements qui ont la classe js-admin-article-delete (que je leur ai donné au prealable dans mon fichier twig, dans la boucle for pour que cela s'applique a tous mes articles°

// console.log('test') console.log(deleteArticleButtons);  je teste si tout fonctionne avec deux console log, une fois que cela s'affiche en console log je poursuis

deleteArticleButtons.forEach((deleteArticleButton) => {
    // pour chaque bouton qui ont la classe js-admin-article-delete on fait un addEventListener
    deleteArticleButton.addEventListener('click', () => {
        // au clic (on click) on execute la callbackfn

        // on prend l'element html -> la popup
        const popup = deleteArticleButton.nextElementSibling;

        //on l affiche avec son style, je passe la pop up trouvée en display block
        popup.style.display = "block";

        // je prends l element html -> un bouton ne pas supprimer sous forme de pop up
        const noDelete =  popup.nextElementSibling

        //je passe le bouton ne pas supprimer sous forme de pop up en display block pour l'afficher avec son style
        noDelete.style.display = "block";

        deleteArticleButton.style.display="none"
        // je fais disparaitre mon bouton de suppression

        noDelete.addEventListener('click', ()=> {
            noDelete.style.display="none";
            popup.style.display = "none";
            deleteArticleButton.style.display="block"

            // au clic  le bouton de suppression  reapparait, la premiere pop up et le bouton ne pas supprimer sous forme de pop up disparaissent

        })


    });
})
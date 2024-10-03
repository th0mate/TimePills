/**
 * Gestion des boutons pour modifier le thème du site
 */
function change_period2(period) {
    var monthly = document.getElementById("monthly2");
    var semester = document.getElementById("semester2");
    var annual = document.getElementById("annual2");
    var selector = document.getElementById("selector");

    if (period === "clair") {
        selector.style.left = 0;
        selector.style.width = monthly.clientWidth + "px";
        selector.style.backgroundColor = "#FF5959";
        selector.innerHTML = `<img src="${soleilImageURL}" alt="">`;
        document.querySelector('.dark').classList.remove('dark');
        StockerCookieTheme("clair");


    } else if (period === "auto") {
        selector.style.left = monthly.clientWidth + "px";
        selector.style.width = semester.clientWidth + "px";
        selector.innerHTML = `<img src="${autoImageURL}" alt="">`;
        selector.style.backgroundColor = "#FF5959";
        StockerCookieTheme("auto");

        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.querySelector('html').classList.add('dark');
        }
    } else {
        selector.style.left = monthly.clientWidth + semester.clientWidth + 1 + "px";
        selector.style.width = annual.clientWidth + "px";
        selector.innerHTML = `<img src="${luneImageURL}" alt="">`;
        selector.style.backgroundColor = "#FF5959";
        document.querySelector('html').classList.add('dark');
        StockerCookieTheme("sombre");
    }
}


/**
 * Stocke le thème choisi dans un cookie (uniquement pour une expérience utilisateur confortable)
 */
function StockerCookieTheme() {
    const selector = document.getElementById("selector");
    if (selector.style.left === "0px") {
        document.cookie = "theme=clair; expires=Fri, 31 Dec 9999 23:59:59 GMT";
    } else if (selector.style.left === "52px") {
        document.cookie = "theme=auto; expires=Fri, 31 Dec 9999 23:59:59 GMT";
    } else {
        document.cookie = "theme=sombre; expires=Fri, 31 Dec 9999 23:59:59 GMT";
    }
}


/**
 * Gestion de l'affichage des boutons par défaut. Utilise les cookies s'il en existe un
 */
if (document.cookie.includes("theme=clair")) change_period2("clair");
else if (document.cookie.includes("theme=sombre")) change_period2("sombre");
else change_period2("auto");

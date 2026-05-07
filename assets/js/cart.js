import { 
    displayCompare, displayCart, displayWishlist, 
    formatPrice, addCartEventListenerToLink, 
    updateHeaderCart, addCompareEventListener, 
    addWishListEventListenerToLink 
} from './library.js';

window.addEventListener("DOMContentLoaded", () => {
    try {
        // ─── Initialisation globale sur toutes les pages ───
        // Met à jour le badge panier dans le header (nombre d'articles)
        updateHeaderCart();
        // Ajoute les écouteurs d'événements sur les boutons "Ajouter au panier"
        addCartEventListenerToLink();
        // Ajoute les écouteurs sur les boutons "Comparer"
        addCompareEventListener();
        // Ajoute les écouteurs sur les boutons "Ajouter à la WishList"
        addWishListEventListenerToLink();

        // ─── Spécifique à la page panier ───
        // Vérifie si on est bien sur la page panier
        const mainContent = document.querySelector('.cart_content');
        // Si l'élément n'existe pas on sort — on n'est pas sur la page panier
        if (!mainContent) return;

        // Récupère le panier et les transporteurs depuis les data attributes HTML
        // Symfony injecte ces données via Twig dans data-cart et data-carriers
        const cart = JSON.parse(mainContent?.dataset?.cart || 'false');
        const carriers = JSON.parse(mainContent?.dataset?.carriers || 'false');

        // Récupère le formulaire et le select des transporteurs
        const form = document.querySelector(".carrier_form form");
        const select = document.querySelector(".carrier_form form select");

        // Si le panier, les transporteurs et le select existent
        if (cart && carriers && select) {
            // Vide le select de façon sécurisée
            select.innerHTML = "";
            carriers.forEach(carrier => {
                // Crée l'option de façon sécurisée sans innerHTML
                const option = document.createElement('option');
                // textContent échappe automatiquement le HTML — protection XSS
                option.value = carrier.id;
                option.textContent = `${carrier.name} ( ${formatPrice(carrier.price / 100)} )`;
                // Pré-sélectionne le transporteur déjà choisi
                if (carrier.id == cart.carrier.id) {
                    option.selected = true;
                }
                // Ajoute l'option au select de façon sécurisée
                select.appendChild(option);
            });
        }

        //  Gestion du changement de transporteur 
        const handleChange = async (event) => {
            event.preventDefault();
            const id = event.target.value;

            if (id) {
                try {
                    // Appel API pour mettre à jour le transporteur en session
                    const response = await fetch('/api/cart/update/carrier/' + id);

                    // Vérifie que la réponse HTTP est correcte
                    if (!response.ok) {
                        throw new Error('Erreur réseau : ' + response.status);
                    }

                    const result = await response.json();

                    if (result.isSuccess) {
                        // Rafraîchit l'affichage du panier avec les nouvelles données
                        displayCart(result.data);
                    } else {
                        console.error('Erreur mise à jour transporteur :', result);
                    }

                } catch (error) {
                    // Capture les erreurs réseau ou JSON invalide
                    console.error('Erreur lors du changement de transporteur :', error);
                }
            }
        };

        // Empêche la soumission du formulaire — géré en AJAX
        form?.addEventListener('submit', (e) => e.preventDefault());
        // Écoute le changement de transporteur dans le select
        select?.addEventListener('change', handleChange);

        // Affiche le panier au chargement de la page
        displayCart(cart);

    } catch (error) {
        // Capture toute erreur inattendue au chargement de la page
        console.error('Erreur initialisation page panier :', error);
    }
});




// import { displayCompare, displayCart, displayWishlist, formatPrice, addCartEventListenerToLink, 
//     updateHeaderCart, addCompareEventListener, addWishListEventListenerToLink } from './library.js';

// window.addEventListener("DOMContentLoaded", () => {
//     // Ceci s'exécute sur TOUTES les pages
//     updateHeaderCart()
//     addCartEventListenerToLink()
//     addCompareEventListener()
//     addWishListEventListenerToLink()
//     // Le reste — spécifique à la page panier
//     let mainContent = document.querySelector('.cart_content')
//     if (!mainContent) return  //  on sort si on n'est pas sur la page panier
//     let cart = JSON.parse(mainContent?.dataset?.cart || 'false')
//     let carriers = JSON.parse(mainContent?.dataset?.carriers || 'false')

//     const form = document.querySelector(".carrier_form form")
//     const select = document.querySelector(".carrier_form form select")
//     if (cart && carriers && select) {
//         select.innerHTML = ""
//         carriers.forEach(carrier => {
//             select.innerHTML += `
//                 <option value="${carrier.id}" ${carrier.id == cart.carrier.id ? 'selected' : ''}>
//                     ${carrier.name} ( ${formatPrice(carrier.price / 100)} )
//                 </option>
//             `
//         })
//     }
//     const handleChange = async (event) => {
//         event.preventDefault()
//         const id = event.target.value
//         if (id) {
//             const response = await fetch('/api/cart/update/carrier/' + id)
//             const result = await response.json()
//             if (result.isSuccess) {
//                 displayCart(result.data)
//             }
//         }
//     }
//     form?.addEventListener('submit', (e) => e.preventDefault())
//     select?.addEventListener('change', handleChange)

//     displayCart(cart)
// })




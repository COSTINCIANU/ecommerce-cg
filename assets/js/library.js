/**
 * Formate un prix en centimes vers une chaîne monétaire lisible
 * Exemple : formatPrice(1999) => "19.99 €"
 */
export const formatPrice = (price) => {
    return Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' })
        .format(price);
}

/**
 * Affiche un message flash temporaire (notification) dans le DOM
 * Joue un son, affiche le message avec le bon style (success/danger),
 * puis efface automatiquement le message après 2 secondes
 */
export const addFlashMessage = (message, status = "success") => {
    let text = `
    <div class="alert alert-${status}" role="alert">
    ${message}
    </div>
    `
    let audio = document.createElement("audio")
    audio.src = "/assets/audios/success.wav"

    audio.play()
    document.querySelector(".notification").innerHTML += text

    setTimeout(() => {
        document.querySelector(".notification").innerHTML = ""
    }, 2000)
}

/**
 * Effectue une requête HTTP GET vers une URL et retourne la réponse en JSON
 * Utilisée pour toutes les requêtes AJAX (panier, compare, wishlist...)
 */
export const fetchData = async (requestUrl) => {
    let response = await fetch(requestUrl)

    return await response.json()
}

/**
 * Gère le click sur un lien d'ajout ou de suppression du PANIER
 * - Empêche la redirection (preventDefault)
 * - Envoie la requête AJAX vers la route cart/add ou cart/remove
 * - Récupère les infos du produit pour afficher son nom dans le flash message
 * - Affiche un message flash selon l'action (ajout = success, suppression = danger)
 * - Met à jour l'affichage du panier (tableau + header)
 */
export const manageCartLink = async (event) => {
    event.preventDefault();
    // let link = event.target.href ? event.target : event.target.parentNode
    // let requestUrl = link.href

    // Fais ça :
    // const link = event.target.closest('a')  // ✅ remonte toujours jusqu'au <a> parent
    // if (!link) return
   
    const link = event.target.closest('a')
    if (!link) return
    let requestUrl = link.href

    // const requestUrl = link.href

    console.log(requestUrl);
    const cart = await fetchData(requestUrl)

    let productId = requestUrl.split('/')[5];
    let product = await fetchData("/product/get/" + productId)

    // console.log({product});

    if (requestUrl.search('/cart/add/') != -1) {
        // add to cart
        if (product) {
            addFlashMessage(`Produit (${product.name}) ajouter au panier !`)
        } else {
            addFlashMessage("Produit ajouter au panier !")
        }
    }
    if (requestUrl.search('/cart/remove/') != -1) {
        // remove from cart
        if (product) {
            addFlashMessage(`Produit (${product.name}) retiré du panier!`, "danger")
        } else {
            addFlashMessage("Produit retiré du panier !", "danger")
        }
    }
    displayCart(cart)
    updateHeaderCart()
}

/**
 * Gère le click sur un lien d'ajout ou de suppression de la liste de COMPARAISON
 * - Empêche la redirection (preventDefault)
 * - Envoie la requête AJAX vers la route compare/add ou compare/remove
 * - Récupère les infos du produit pour afficher son nom dans le flash message
 * - Affiche un message flash selon l'action
 * - Rafraîchit le tableau de comparaison via displayCompare()
 */
export const manageCompareLink = async (event) => {
    event.preventDefault();
    // console.log("manageCompareLink");
    let link = event.target.href ? event.target : event.target.parentNode
    let requestUrl = link.href

    console.log(requestUrl);

    const compare = await fetchData(requestUrl)




    let productId = requestUrl.split('/')[5];
    let product = await fetchData("/product/get/" + productId)

    if (requestUrl.search('/compare/add/') != -1) {
        // add to cart
        if (product) {
            addFlashMessage(`Product (${product.name}) ajouté à la liste de comparaison !`)
        } else {
            addFlashMessage("Produit ajouté au panier !")
        }
    }
    if (requestUrl.search('/compare/remove/') != -1) {
        // remove from cart
        if (product) {
            addFlashMessage(`Produit (${product.name}) supprimé de la liste de comparaison !`, "danger")
        } else {
            addFlashMessage("Produit supprimé de la liste de comparaison !", "danger")
        }
    }


    displayCompare()
}

/**
 * Gère le click sur un lien d'ajout ou de suppression de la WISHLIST
 * - Empêche la redirection (preventDefault)
 * - Envoie la requête AJAX vers la route wishlist/add ou wishlist/remove
 * - Récupère les infos du produit pour afficher son nom dans le flash message
 * - Affiche un message flash selon l'action
 * - Rafraîchit le tableau de wishlist via displayWishlist()
 */
export const manageWishListLink = async (event) => {
    event.preventDefault();
    console.log("manageWishListLink");
    let link = event.target.href ? event.target : event.target.parentNode
    let requestUrl = link.href

    console.log({ requestUrl });

    // écoute en arrier plan d'evenement
    const wishlist = await fetchData(requestUrl)
    console.log(wishlist);




    let productId = requestUrl.split('/')[5];
    let product = await fetchData("/product/get/" + productId)

    if (requestUrl.search('/wishlist/add/') != -1) {
        // add to cart
        if (product) {
            addFlashMessage(`Produit (${product.name}) ajouté à la liste de souhaits !`)
        } else {
            addFlashMessage("Produit ajouté à la liste de souhaits !")
        }
    }
    if (requestUrl.search('/wishlist/remove/') != -1) {
        // remove from cart
        if (product) {
            addFlashMessage(`Produit (${product.name}) supprimé de la liste de souhaits
 !`, "danger")
        } else {
            addFlashMessage("Produit supprimé de la liste de souhaits !", "danger")
        }
    }

    // Et j'appele et affiche la wishList
    displayWishlist(wishlist)
}

/**
 * Affiche/rafraîchit le tableau de comparaison dans la page compare
 * - Si aucun compare n'est passé en paramètre, le récupère via /compare/get
 * - Reconstruit les lignes du tableau (image, nom, prix, ajout panier, suppression)
 * - Réattache les événements click sur les liens de comparaison
 */
export const displayCompare = async (compare = null) => {

    let tbody = document.querySelector('table.compare_table tbody')
    if (tbody) {

        if (!compare) {
            compare = await fetchData("/compare/get")
        }

        if (compare) {
            let imageContainer = document.querySelector('table.compare_table tbody tr.pr_image')
            imageContainer.innerHTML = ""
            let nameContainer = document.querySelector('table.compare_table tbody tr.pr_title')
            nameContainer.innerHTML = ""
            let priceContainer = document.querySelector('table.compare_table tbody tr.pr_price')
            priceContainer.innerHTML = ""
            let addToCart = document.querySelector('table.compare_table tbody tr.pr_add_to_cart')
            addToCart.innerHTML = ""
            let romoveFromCart = document.querySelector('table.compare_table tbody tr.pr_remove')
            romoveFromCart.innerHTML = ""
            compare.forEach((product) => {
                imageContainer.innerHTML += `
                <td class="row_img">
                <img src="/assets/images/products/${product.imageUrls[0]}" alt="compare-img">
                </td> 
                `
                nameContainer.innerHTML += `
                <td class="product_name">
                    <a href="shop-product-detail.html">${product.name}</a>
                </td>
                `
                priceContainer.innerHTML += `
                <td class="product_price">
                <span class="price">${formatPrice(product.soldePrice / 100)}</span></td>
                `
                addToCart.innerHTML += `
                <td class="row_btn">
                <a href="/cart/add/${product.id}" 
                class="btn btn-fill-out add-to-cart"><i
                class="icon-basket-loaded"></i> Ajouter au panier</a>
                </td>
                `
                romoveFromCart.innerHTML += `
                <td class="row_remove">
                    <a href="/compare/remove/${product.id}" class="remove_compare_item">
                        <span>Supprimer</span> <i class="fa fa-times"></i>
                    </a>
                </td>
                `

            });
        }
    }
    addCompareEventListener()
}

/**
 * Attache les événements click sur tous les liens de comparaison
 * (bouton "ajouter à comparer" et bouton "supprimer de la comparaison")
 * Appelée après chaque mise à jour du DOM pour que les nouveaux liens soient actifs
 */
export const addCompareEventListener = () => {
    let links = document.querySelectorAll(".add-to-compare, .compare_table .remove_compare_item")
    // console.log({ links });
    links.forEach(link => {
        link.addEventListener("click", manageCompareLink)
    });
}

/**
 * Attache les événements click sur tous les liens de la wishlist
 * (bouton "ajouter à la wishlist" et bouton "supprimer de la wishlist")
 * Appelée après chaque mise à jour du DOM pour que les nouveaux liens soient actifs
 */
export const addWishListEventListenerToLink = () => {
    let links = document.querySelectorAll(".add-to-wishlist, .wishlist_table .remove-to-wishlist")

    links.forEach(link => {
        link.addEventListener("click", manageWishListLink)
    });
}

/**
 * Attache les événements click sur tous les liens du panier
 * - Les liens dans le tableau du panier (tbody)
 * - Les boutons "ajouter au panier", "supprimer du header", "ajouter au panier" partout sur le site
 * Appelée après chaque mise à jour du DOM pour que les nouveaux liens soient actifs
 */
export const addCartEventListenerToLink = () => {
    let links = document.querySelectorAll('.shop_cart_table tbody a')
    links.forEach((link) => {
        link.addEventListener("click", manageCartLink)
    })

    let add_to_cart_links = document.querySelectorAll('a.add-to-cart, a.item_remove,  a.btn-addtocart')
    add_to_cart_links.forEach((link) => {
        link.addEventListener("click", manageCartLink)
    })
}


/**
 * Affiche/rafraîchit le tableau du panier dans la page panier
 * - Met à jour le header panier via updateHeaderCart()
 * - Si un panier est passé en paramètre, reconstruit les lignes du tableau
 * - Calcule et affiche les totaux (sous-total, frais de port, total)
 * - Réattache les événements click sur les liens du panier
 */
export const displayCart = (cart = null) => {
    updateHeaderCart(cart)
    addCartEventListenerToLink()
    if (!cart) {
        return
    }
    console.log(cart);
    let tbody = document.querySelector('.shop_cart_table tbody')
    let cart_sub_total_ht_amount = document.querySelector('.cart_sub_total_ht_amount')
    let cart_sub_total_taxe_amount = document.querySelector('.cart_sub_total_taxe_amount')
    let cart_shipping_total_amount = document.querySelector('.cart_shipping_total_amount')
    let cart_total_amount = document.querySelector('.cart_total_amount')
    if (tbody) {
        tbody.innerHTML = ""
        cart.items.forEach((item) => {
            let { product, quantity, sub_total, taxe, sub_total_ht } = item 
            console.log({item});
            let content = `
             <tr>
                <td class="product-thumbnail">
                <a>
                    <img width="50" alt="product1" src="/assets/images/products/${product.imageUrls[0]}">
                </a>
                </td>
                <td data-title="Product" class="product-name">
                    <a>${product.name}</a>
                    </td>
                <td data-title="Price" class="product-price">
                    ${formatPrice(product.soldePrice / 100)}
                </td>
                <td data-title="Quantity" class="product-quantity">
                    <div class="quantity">
                        <a href="/cart/remove/${product.id}/1">
                            <input type="button" value="-" class="minus">
                        </a>
                        <input type="text" name="quantity" value="${quantity}" title="Qty" size="4" class="qty">
                        <a href="/cart/add/${product.id}/1">
                            <input type="button" value="+" class="plus">
                        </a>
                    </div>
                </td>
                <td data-title="Total" class="product-subtotal">
                    ${formatPrice(taxe / 100)} 
                </td>
                  <td data-title="Total" class="product-subtotal">
                    ${formatPrice(sub_total_ht / 100)} 
                </td>
                  <td data-title="Total" class="product-subtotal">
                    ${formatPrice(sub_total / 100)} 
                </td>
                <td data-title="Retirer" class="product-remove">
                    <a href="/cart/remove/${product.id}/${item.quantity}">
                        <i class="ti-close"></i>
                    </a>
                </td>
            </tr>
             `
            tbody.innerHTML += content
        });

        cart_sub_total_ht_amount.innerHTML = formatPrice(cart.sub_total_ht / 100)
        cart_sub_total_taxe_amount.innerHTML = formatPrice(cart.taxe / 100)
        cart_shipping_total_amount.innerHTML = formatPrice(cart.carrier.price/100)
        cart_total_amount.innerHTML = formatPrice( (cart.sub_total+cart.carrier.price) / 100)
      
    }
    addCartEventListenerToLink()

}


/**
 * Affiche/rafraîchit le tableau de la wishlist dans la page wishlist
 * - Si un wishlist est passé en paramètre, reconstruit les lignes du tableau
 * - Réattache les événements click sur les liens de la wishlist
 */
export const displayWishlist = (wishlist = null) => {
   
    addWishListEventListenerToLink()
    if (!wishlist) {
        return
    }

    let tbody = document.querySelector('.wishlist_table tbody')

    if (tbody) {
        tbody.innerHTML = ""
        wishlist.forEach((product) => {
            let content = `
            <tr>
            <td class="product-thumbnail">
                <a href="#">
                    <img width="50" height="50" alt="product1" src="/assets/images/products/${product.imageUrls[0]}">
                </a>
            </td>
            <td data-title="Product" class="product-name">
                <a href="#">
                    ${ product.name }
                </a>
            </td>
            <td data-title="Price" class="product-price">
                ${ formatPrice(product.soldePrice/100) }
            </td>
           
            <td data-title="Stock Status" class="product-stock-status">
                ${ product.stock ?? 'Non disponible' }
                <span class="badge badge-pill badge-${product.stock ? 'success' : 'danger'}">
                    ${ product.stock ? 'En Stock' : 'Rupture de stock' }
                </span>
            </td>
            <td class="product-add-to-cart">
                 ${ product.stock ? 
                        `<a href="/cart/add/${product.id}/1" class="btn btn-fill-out btn-addtocart">
                            <i class="icon-basket-loaded"></i> Ajouter au panier
                        </a>` 
                        : 
                        `<span class="text-danger">Rupture de stock</span>`
                    }
            </td>
            <td >
                <a href="/wishlist/remove/${product.id}" class="remove-to-wishlist">
                    <i class="ti-close"></i>
                </a>
            </td>
        </tr>
             `
            tbody.innerHTML += content
        });

       
    }
    addWishListEventListenerToLink()

}


/**
 * Met à jour le mini panier dans le header (icône panier en haut de page)
 * - Si aucun panier n'est passé, le récupère via /cart/get
 * - Met à jour le compteur d'articles, le prix total et la liste des produits
 * - Réattache les événements click sur les liens du panier (notamment item_remove)
 */
export const updateHeaderCart = async (cart = null) => {
    let cart_count = document.querySelector(".cart_count")
    let cart_list = document.querySelector(".cart_list")
    let cart_price_value_ht = document.querySelector(".cart_price_value_ht")
    
    let cart_taxe_value = document.querySelector(".cart_taxe_value")
    let cart_price_value_ttc = document.querySelector(".cart_price_value_ttc")

    if (!cart) {
        // cart not found
        cart = await fetchData("/cart/get")
    }


    // cart data found
    cart_count ? cart_count.innerHTML = cart?.cart_count : null
    cart_price_value_ht ? cart_price_value_ht.innerHTML = formatPrice(cart.sub_total_ht / 100) : null
    cart_taxe_value ? cart_taxe_value.innerHTML = formatPrice(cart.taxe / 100) : null
    cart_price_value_ttc ? cart_price_value_ttc.innerHTML = formatPrice(cart.sub_total / 100) : null
    
    
    if(cart_list){
        cart_list.innerHTML = ""
        cart.items.forEach(item => {
            let { product, quantity, sub_total } = item
            cart_list.innerHTML += `
                <li>
                <a href="/cart/remove/${product.id}/${quantity}" class="item_remove">
                    <i class="ion-close"></i>
                </a>
                <a href="/product/${product.slug}">
                    <img width="50" height="50" alt="cart_thumb1" src="/assets/images/products/${product.imageUrls[0]}">
                    ${product.name}
                </a>
                <span class="cart_quantity"> ${quantity} x
                    <span class="cart_amount">
                        <span class="price_symbole">${formatPrice(product.soldePrice / 100)}</span> =
                    </span>
                    <span class="cart_amount">
                        <span class="price_symbole">${formatPrice(product.soldePrice * quantity / 100)}</span>
                    </span>
                </span>
            </li>
                `
        })

    }


    addCartEventListenerToLink()

}

export function initCart() {}
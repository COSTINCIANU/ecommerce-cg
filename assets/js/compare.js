import { formatPrice,
    displayCompare,
    addCompareEventListener,
     addFlashMessage,
     fetchData, 
     manageCartLink, 
     addCartEventListenerToLink,
     initCart,
     updateHeaderCart,
     manageCompareLink } from './library.js';

window.onload = () => {
    

    let mainContent = document.querySelector('.compare_container')

    let compare = JSON.parse(mainContent?.dataset?.compare || false)

    addCompareEventListener()

    displayCompare(compare)

}
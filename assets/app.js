import './stimulus_bootstrap.js';
import './styles/app.css';
import { Turbo } from '@hotwired/turbo';

// Désactiver Turbo Drive complètement
Turbo.session.drive = false;

/*
 * ========================
 * CSS
 * ========================
 */
import './css/animate.css';
import './bootstrap/css/bootstrap.min.css';
import './css/all.min.css';
import './css/ionicons.min.css';
import './css/themify-icons.css';
import './css/linearicons.css';
import './css/flaticon.css';
import './css/simple-line-icons.css';

import './owlcarousel/css/owl.carousel.min.css';
import './owlcarousel/css/owl.theme.css';
import './owlcarousel/css/owl.theme.default.min.css';

import './css/magnific-popup.css';
import './css/slick.css';
import './css/slick-theme.css';

import './css/style.css';
import './css/responsive.css';

/*
 * ========================
 * JS (ordre IMPORTANT)
 * ========================
 */

// jQuery en premier
import './js/jquery-3.6.0.min.js';

// Bootstrap dépend de Popper
import './js/popper.min.js';
import './bootstrap/js/bootstrap.min.js';

// Plugins
import './owlcarousel/js/owl.carousel.min.js';
import './js/magnific-popup.min.js';
import './js/waypoints.min.js';
import './js/parallax.js';
import './js/jquery.countdown.min.js';
import './js/imagesloaded.pkgd.min.js';
import './js/isotope.min.js';
import './js/jquery.dd.min.js';
import './js/slick.min.js';
import './js/jquery.elevatezoom.js';

// Script global du thème
import './js/scripts.js';

// Tes scripts
import './js/cart.js';
import './js/compare.js';
import './js/main.js';
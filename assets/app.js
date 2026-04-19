import './stimulus_bootstrap.js';
import './styles/app.css';
import { Turbo } from '@hotwired/turbo';

// Désactiver Turbo Drive complètement
Turbo.session.drive = false;

<?php
// Si uninstall.php n'est pas appelé par WordPress, quitter le script.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Nom des options à supprimer
$options = array(
    'barkapay_api_key',
    'barkapay_api_secret',
    'barkapay_sci_key',
    'barkapay_sci_secret',
    // Ajoutez ici toutes les autres options que votre plugin utilise
);

// Supprimer chaque option
foreach ( $options as $option ) {
    delete_option( $option );
    // Pour les installations multisites
    delete_site_option( $option );
}
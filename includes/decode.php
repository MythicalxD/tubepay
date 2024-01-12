<?php

// Function to decrypt data using OpenSSL and a private key
function decrypt($encryptedData)
{
    include_once dirname(__FILE__) . '/Constants.php';

    // Create a resource for the private key
    $privateKeyResource = openssl_pkey_get_private(KEY);

    // Check if the private key is valid
    if ($privateKeyResource === false) {
        die('Unable to load private key');
    }

    // Decrypt the data
    $decryptedData = '';
    $success = openssl_private_decrypt(base64_decode($encryptedData), $decryptedData, $privateKeyResource);

    // Check if decryption was successful
    if (!$success) {
        die('Decryption failed');
    }

    return $decryptedData;
}

// Example usage
$encryptedDataFromDart = "OhSNiwMWnHqZwyIDBmZbfP0HHLnz8rkAYmzyntEy45chvGOBQCK85SpgbHFpnpFZoJt+Y14W7tCRhOyxLj14noz9i4VZh29TnjvjYeJukQ39VWotRmh1G8DkPXo6PHksQrzFxEYZGbuvWfdGzoWZuuRqc3cwh/nJbNKeCChzrgwefodeD6tIbHULo3Bcnq8Uaw+Xk7FpqG9ljHJECC2Vswy9MU8iAvJzqqRfZkl95nJpA/QcppwRbqVYOPb7YZFGFjTOgRa493RETx0LrYuwKW8njAvZWSx5uws6QQkexwAjgm4H4jdpd6pf+n4duN9YEG6cUVZ5Kc44rTqRLai9Iw==";
?>
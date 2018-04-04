<?php
ini_set('display_errors', 0); // 0 = hide errors; 1 = display errors

$client_sites_arr = array("agency.com", "batman.com", "arsari.com");
$sites_arr_length = count($client_sites_arr);

echo "=== Testing sites ===\n";
echo "\n";

for($x = 0; $x < $sites_arr_length; $x++) {
    $url = "http://www.{$client_sites_arr[$x]}";
    $eval_ssl = get_headers($url, 1)['Location']; // return an associate array with site information. if the key 'Location' is present, site is secure.

    if ($eval_ssl) {
        $ssl = "secure";
        echo $eval_ssl, "\t=>\t", $ssl, "\n";
    } else {
        $ssl = "non-secure";
        echo $url, "\t=>\t", $ssl, "\n";
    }
}

echo "\n=== End of testing ===";

// print_r(get_headers($url, 1));
?>
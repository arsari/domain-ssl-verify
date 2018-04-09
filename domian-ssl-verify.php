<?php
/**
 * PHP Version 7.1.9
 *
 * This script read a csv file with a list of domains and fetches all the
 * headers sent by a domain server in response to a HTTP request from an
 * array of domains and returning an associate array with each domain
 * header information
 *
 * @category HTTP_Request
 * @package  None
 * @author   Arturo Santiago <asantiago@arsari.com>
 * @license  MIT License
 * @link     https://www.arsari.githib.io
 */
ini_set('display_errors', 0); // 0 = hide errors; 1 = display errors

// read csv file content
$rows = file('domains.csv');
$data = array_pop($rows);
$clients_domains = str_getcsv($data);
$domains_arr_length = count($clients_domains);

// global variables definition
// $clients_domains = array("micasa.net", "myhome.net", "agency.com", "batman.com", "arsari.com", "aviation.com", "housekeeping.com");
// $domains_arr_length = count($clients_domains);
$ssl = 'Secure';
$non_ssl = 'Non-Secure';
$separator = ">\t";

/**
 * Function to evaluated a domain array redirection
 *
 * @param String $url      domain with redirection
 * @param Array  $url_eval array of redirected domains
 *
 * @return None
 */
function urlRedirect($url, $url_eval)
{
    echo $url . "\t[ Domain redirect to: ]\n";

    for ($y = 0; $y < 3; $y++) {
        $url_eval_arr = $url_eval[$y];

        if ($url_eval_arr === null or strpos($url_eval_arr, 'http') === false) {
            continue;
        } elseif (strpos($url_eval_arr, 'https')) {
            echo "\t" . str_pad($url_eval_arr, 44, " .") . $GLOBALS['separator'] . $GLOBALS['ssl'] . "\n";
        } else {
            echo "\t" . str_pad($url_eval_arr, 44, " .") . $GLOBALS['separator'] . $GLOBALS['non_ssl'] . "\n";
        }
    }
}

// main sript
echo str_pad(" Testing Domains ", 66, "=", STR_PAD_BOTH) . "\n";
echo "\n";

for ($x = 0; $x < $domains_arr_length; $x++) {
    $url = "http://www.{$clients_domains[$x]}"; // domain url to evaluate
    $headers = get_headers($url, 1); // returns an associated array with index keys of the domain server headers
    $url_eval = $headers['Location']; // look for the value of the index key 'Location'

    if (strpos($headers[0], '404')) {
        echo str_pad($url, 52, " .") . $separator . "Not Found", "\n";
    } elseif (is_array($url_eval)) {
        urlRedirect($url, $url_eval);
    } elseif ($url_eval) {
        echo str_pad($url_eval, 52, " .") . $separator . $ssl . "\n";
    } else {
        echo str_pad($url, 52, " .") . $separator . $non_ssl . "\n";
    }
}

echo "\n";
echo str_pad(" End Testing ", 66, "=", STR_PAD_BOTH) . "\n";

// print_r(get_headers($url, 1));

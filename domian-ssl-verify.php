<?php
/**
 * PHP Version 7.1.9
 *
 * This script do an HTTP request of a list of domain names
 * storage in a CSV file or SQL database and fetches all the
 * headers sent by the domain server returning an associate
 * array with each domain header information. When the list
 * is on an SQL database, the script update the table of
 * the list of domains adding to the database the resulting
 * SSL status in in an existing column named SSLcertificate.
 *
 * @category HTTP_Request
 * @package  None
 * @author   Arturo Santiago <asantiago@arsari.com>
 * @license  MIT License
 * @link     https://arsari.github.io
 */

// disable warnings and notices (0 = hide errors; -1 = display errors)
error_reporting(0);

// global variables
$dead = '404 Not Found';
$ssl = 'Secure';
$non_ssl = 'Non-Secure';
$redirect = 'Redirect > ';
$blocked = '403 Forbidden';
$down = 'Reading Error';
$separator = ">\t";
$header = str_pad(" Testing Domains ", 77, "=", STR_PAD_BOTH) . "\n";
$footer = str_pad(" End Testing ", 77, "=", STR_PAD_BOTH) . "\n";

/**
 * Function to evaluated a domains
 *
 * @param Array $domains array with list of domains names
 *
 * @return String $ssl_status return ssl status
 */
function domainsEval($domains)
{
    $domains_arr_length = count($domains);

    for ($x = 0; $x < $domains_arr_length; $x++) {
        if ((substr_count($domains[$x], ".") > 1)) {
            $url = "http://" . $domains[$x] . "/"; // domain url to evaluate
        } else {
            $url = "http://www." . $domains[$x] . "/"; // domain url to evaluate
        }
        $headers = get_headers($url, 1); // returns an associated array with index keys of the domain server headers

        // look for the value of the index key 'Location' or 'location' in the headers request
        if ($headers['location'] !== null) {
            $url_eval = $headers['location'];
        } else {
            $url_eval = $headers['Location'];
        }

        // evaluate server headers return and index key (L)ocation
        if (strpos($headers[0], '404')) {
            $ssl_status = $GLOBALS['dead'];
            echo str_pad($url, 52, " .") . $GLOBALS['separator'] . $GLOBALS['dead'] . "\n";
        } elseif (strpos($headers[0], '403')) {
            $ssl_status = $GLOBALS['blocked'] . $GLOBALS['ssl'];
            echo str_pad($url, 52, " .") . $GLOBALS['separator'] . $GLOBALS['blocked'] . "\n";
        } elseif (is_array($url_eval)) {
            $ssl_status = urlRedirect($url, $url_eval);
        } elseif (strpos($url_eval, 'https') !== false) {
            if (strpos($url_eval, $domains[$x]) === false) {
                echo $url . "  [Domain Redirect]\n";
                echo "\t" . str_pad($url_eval, 44, " .") . $GLOBALS['separator'] . $GLOBALS['redirect'] . $GLOBALS['ssl'] . "\n";
            } else {
                $ssl_status = $GLOBALS['ssl'];
                echo str_pad($url_eval, 52, " .") . $GLOBALS['separator'] . $GLOBALS['ssl'] . "\n";
            }
        } elseif ($url_eval !== null) {
            $ssl_status = $GLOBALS['non_ssl'];
            echo str_pad($url_eval, 52, " .") . $GLOBALS['separator'] . $GLOBALS['non_ssl'] . "\n";
        } elseif ($url_eval === null and ((strpos($headers[0], '200') !== false) or (strpos($headers[0], '301') !== false))) {
            $ssl_status = $GLOBALS['non_ssl'];
            echo str_pad($url, 52, " .") . $GLOBALS['separator'] . $GLOBALS['non_ssl'] . "\n";
        } else {
            $ssl_status = $GLOBALS['down'] . $GLOBALS['non_ssl'];
            echo str_pad($url, 52, " .") . $GLOBALS['separator'] . $GLOBALS['down'] . "\n";
        }
    }
    return $ssl_status;
}

/**
 * Function to evaluated a domain array redirection
 *
 * @param String $url      domain with redirection
 * @param Array  $url_eval array of redirected domains
 *
 * @return String $ssl_status return ssl status
 */
function urlRedirect($url, $url_eval)
{
    echo $url . "  [Domain redirect]\n";

    for ($y = 0; $y < 3; $y++) {
        $url_eval_arr = $url_eval[$y];

        if ($url_eval_arr === null or strpos($url_eval_arr, 'http') === false) {
            continue;
        } elseif (strpos($url_eval_arr, 'https') !== false) {
            $ssl_status = $GLOBALS['redirect'] . $GLOBALS['ssl'];
            echo "\t" . str_pad($url_eval_arr, 44, " .") . $GLOBALS['separator'] . $GLOBALS['redirect'] . $GLOBALS['ssl'] . "\n";
        } else {
            $ssl_status = $GLOBALS['redirect'] . $GLOBALS['non_ssl'];
            echo "\t" . str_pad($url_eval_arr, 44, " .") . $GLOBALS['separator'] . $GLOBALS['redirect'] . $GLOBALS['non_ssl'] . "\n";
        }
    }
    return $ssl_status;
}

/**
 * Function to read a CSV file
 *
 * @return None
 */
function readCSV()
{
    // User input file name
    echo "\nEnter file name without '.csv': ";
    $csv_file = trim(fgets(STDIN));
    echo "\n";

    $file_name = $csv_file . ".csv";

    // Read CSV file
    $row = 1;
    if (($handle = fopen($file_name, "r")) !== false) { // read csv file content
        echo $GLOBALS[header] . "\n";
        while (($domains = fgetcsv($handle, 1000, ",")) !== false) {
            domainsEval($domains);
            $row++;
        }
        fclose($handle);
    } else {
        echo "*** ERROR: Failed to connect to file. ***\n";
    }
}

/**
 * Function to read an SQL database
 *
 * @return None
 */
function readDB()
{
    // Sql credentials and variables input
    echo "[Database login credentials and db information]";
    echo "Enter [HOST] name: ";
    $host = trim(fgets(STDIN));
    echo "Enter [USER] name: ";
    $user = trim(fgets(STDIN));
    echo "Enter [PASSWORD] for user: ";
    $password = trim(fgets(STDIN));
    echo "Enter [DATABASE] name: ";
    $db = trim(fgets(STDIN));
    echo "Enter [TABLE] name: ";
    $table = trim(fgets(STDIN));
    echo "Enter [COLUMN] name: ";
    $column = trim(fgets(STDIN));
    echo "\n";

    // db testing credentials
    // $host = "";
    // $user = "asantiago";
    // $password = "";
    // $db = "Domains";
    // $table = "Names";
    // $column = "DomainName";

    // Initiate sql connection
    $conn = mysqli_connect($host, $user, $password, $db);

    // Check connection
    if (mysqli_connect_errno()) {
        echo "\n*** ERROR: Failed to connect to database [" . mysqli_connect_error() . "]***\n";
    } else {
        // Look on table
        $sql_query_r = "SELECT {$column} FROM {$table} ORDER BY {$table}.{$column}";

        echo "\n" . $GLOBALS[header] . "\n";

        if ($result = mysqli_query($conn, $sql_query_r)) {
            // Fetch one and one row
            while ($domains = mysqli_fetch_row($result)) {
                $ssl_status = domainsEval($domains);
                // insert domain ssl status into db
                $sql_query_w = "UPDATE {$table} SET SSLcertificate = '{$ssl_status}' WHERE {$column} = '{$domains[0]}'";
                mysqli_query($conn, $sql_query_w);
            }
            // Free result set
            mysqli_free_result($result);
        }
    }
    // End sql connection
    mysqli_close($conn);
}

// Initial output
$input = -1;

while ($input != 0) {
    echo "\nWhere is the list of domians to evaluate?\n";
    echo "1 => CSV File\n";
    echo "2 => SQL Database\n";
    echo "0 => Exit\n";
    echo "\nSelect option: ";

    $input = trim(fgets(STDIN));

    if ($input == '1') {
        echo "\n<<< Testing from CSV file >>>\n";
        readCSV();
        echo "\n" . $footer;
    } elseif ($input == '2') {
        echo "\n<<< Testing from Database >>>\n";
        readDB();
        echo "\n" . $footer;
    } elseif ($input == '0') {
        exit("\n<<< Good Bye!! >>>\n\n");
    } else {
        echo "\n*** Error: Option Invalid ***\n";
    }
}

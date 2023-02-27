<?php
error_reporting(E_ERROR);

$file = 'data/data.html';

$domDoc = new DOMDocument();
$domDoc->loadHTMLFile($file);

// Create our xpath object and pass in pur xpath value
$xpathdoc = new DOMXpath($domDoc);
$elements = $xpathdoc->query("//li[@role='doc-endnote']//a[@rel='external']");

$aDomains = [];

// Loop through and parse out each domain, increasing the count for each domain in aDomains
foreach ($elements as $element) { 
    $domain = parse_url($element->getAttribute('href'),PHP_URL_HOST);

    $aDomains[$domain] += 1;
}
//arsort($aDomains);
ksort($aDomains);

// loop through our array and create a tabular report of the domains
$html = '';
foreach ($aDomains as $domain=>$counter) {
    $html .= '<tr><td>'.$domain.'</td><td>'.$counter.'</td></tr>';
}
?>
<html>
    <head>
        <title>Domain Report</title>
        <style>
            body {
                font-family: arial;
            }
            table {
                border-collapse: collapse;
            }
            td, th {
                border: 1px solid;
                padding: 5px;
                text-align: left;
            }
        </style>
    </head>
    <body>
        <h1>Domain Usage Report</h1>

        <p>Simple report to detail how often domains are used within our data</p>

        <table>
            <tr>
                <th>Domain</th>
                <th>Count</th>
            </tr>
            <?php
            print $html;
            ?>
        </table>
    </body>
</html>
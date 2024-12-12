<?php
/**
 * Function to generate an HTML table from an array of objects
 *
 * @param array $data Array of objects representing the table data
 * @return string HTML table string
 */
function makeTable($data) {
    if (empty($data)) {
        return "No data to display";
    }

    $keys = array_keys(get_object_vars($data[0]));
    $header = "<tr>";
    foreach ($keys as $key) {
        $header .= "<th>" . ucfirst($key) . "</th>";
    }
    $header .= "</tr>";

    $rows = "";
    foreach ($data as $obj) {
        $rows .= "<tr>";
        foreach ($keys as $key) {
            $rows .= "<td>" . $obj->$key . "</td>";
        }
        $rows .= "</tr>";
    }

    return "<table>$header$rows</table>";
}
?>
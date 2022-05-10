<?php
/*
Plugin Name: Ip-Viewer
Plugin URI: https://github.com/faab007nl/Yourls-Ip-Viewer
Description: A plugin that allows you to view the IP address of the visitor.
Version: 1.0
Author: Faab007NL
*/

// Register your plugin admin page
yourls_add_action( 'plugins_loaded', 'plugin_init' );
function plugin_init() {
    yourls_register_plugin_page( 'ip-viewer', 'Ip Viewer', 'plugin_display_page' );
}

// The function that will draw the admin page
function plugin_display_page() {
    echo "
        <style>
            .table-container{
              padding: 0 35px 0 10px
            }
            
            table {
              font-family: arial, sans-serif;
              border-collapse: collapse;
              width: 100%;
            }
            
            td, th {
              border: 1px solid #dddddd;
              text-align: left;
              padding: 8px;
            }
            
            tr:hover {
              background-color: rgba(255,255,255,0.2);
            }
            
            .select-form{
                margin-bottom: 20px;
            }
            .select-label{
                padding: 0 35px 0 10px
            }
            .page-title{
                padding: 0 35px 0 10px;
                float: none;
            }
            .results-title{
                position: relative; 
            }
            .reload-icon{
                width: 20px;
                height: 20px;
                fill: white;
                position: absolute;
                top: 4px;
                right: 5px;
                cursor: pointer;
            }
            textarea{
                padding: 10px;
                border: none!important;
                background: #313131!important;
                color: #dcdcdc!important;
                font-size: 1em!important;
                outline: none;
                margin: 0 5px!important;
                border-radius: 0!important;
                width: calc(100% - 30px);
            }
        </style>
    ";
    $reloadIcon = '<svg class="reload-icon" onclick="window.location.reload()" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M464 16c-17.67 0-32 14.31-32 32v74.09C392.1 66.52 327.4 32 256 32C161.5 32 78.59 92.34 49.58 182.2c-5.438 16.81 3.797 34.88 20.61 40.28c16.89 5.5 34.88-3.812 40.3-20.59C130.9 138.5 189.4 96 256 96c50.5 0 96.26 24.55 124.4 64H336c-17.67 0-32 14.31-32 32s14.33 32 32 32h128c17.67 0 32-14.31 32-32V48C496 30.31 481.7 16 464 16zM441.8 289.6c-16.92-5.438-34.88 3.812-40.3 20.59C381.1 373.5 322.6 416 256 416c-50.5 0-96.25-24.55-124.4-64H176c17.67 0 32-14.31 32-32s-14.33-32-32-32h-128c-17.67 0-32 14.31-32 32v144c0 17.69 14.33 32 32 32s32-14.31 32-32v-74.09C119.9 445.5 184.6 480 255.1 480c94.45 0 177.4-60.34 206.4-150.2C467.9 313 458.6 294.1 441.8 289.6z"/></svg>';

    echo "<div class='ip-viewer-container'>";
    echo "<h1 class='page-title'>Ip Viewer</h1>";

    $shortcode = $_GET['shortcode'];
    $table = YOURLS_DB_TABLE_URL;
    $sql = "SELECT * FROM `$table`;";
    $allUrls = yourls_get_db()->fetchAll($sql);

    echo '
        <form class="select-form" action="" method="get">
            <input type="hidden" name="page" value="ip-viewer">
            <div class="custom-select" style="width:200px;">
                <label class="select-label" for="shortcode">Short URL:</label>
                <select name="shortcode" id="shortcode" onchange="this.form.submit()">
                    <option value="">Select Short URL</option>
                    ';
                    foreach($allUrls as $url){
                        echo '<option value="'.$url['keyword'].'" '; if($url['keyword'] == $shortcode) {echo 'selected';} echo' >' .$url['keyword'].' ('.$url['url'].')</option>';
                    }
                    echo '
                </select>
            </div>
        </form>
    ';

    if(empty($shortcode)){
        echo "
            <div class='table-container'>
                <h2>Please select a short URL.</h2>
            </div>
        ";
        echo "</div>";
        return;
    }

    $table = YOURLS_DB_TABLE_LOG;
    $sql = "SELECT * FROM `$table` WHERE `shorturl` = :code;";
    $binds = array('code' => $shortcode);
    $ipData = yourls_get_db()->fetchAll($sql, $binds);

    echo "
        <div class='table-container'>
            <h2 class='results-title'>Found ".count($ipData)." results for ".$shortcode.": ".$reloadIcon."</h2>
            <table>
                <tr>
                    <th>Id</th>
                    <th>Shorturl</th>
                    <th>Refferer</th>
                    <th>User Agent</th>
                    <th>Ip Address</th>
                    <th>Country</th>
                    <th>Date Time</th>
                </tr>
                ";
                    foreach ($ipData as $row) {
                        echo "<tr>";
                        echo "<td>" . $row['click_id'] . "</td>";
                        echo "<td><a href='".YOURLS_SITE.$row['shorturl']."+' target='_blank'>" . $row['shorturl'] . "</a></td>";
                        echo "<td>" . $row['referrer'] . "</td>";
                        echo "<td width='99%'><textarea readonly rows='2'>".$row['user_agent']."</textarea></td>";
                        echo "<td><input value='".$row['ip_address']."' readonly/></td>";
                        echo "<td><input style='width: 30px;' value='".$row['country_code']."' readonly/></td>";
                        echo "<td><input value='".$row['click_time']."' readonly/></td>";
                        echo "</tr>";
                    }
                echo "
            </table>
        </div>
    ";

    echo "</div>";

}
?>

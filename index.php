<?php
include "config.php";
include "home.php";
include "libs/Parsedown.php";

function load_page($p, $categories, $articles, $page_title)
{
    $smart = $date_str_s = $path = "";
    switch($p)
    {
        case "article":
            $path = isset($_GET["path"]) ? $_GET["path"] : "";
            $path = urldecode($path);
            $category = isset($_GET["category"]) ? $_GET["category"] : "";
            $category = urldecode($category);
            if($category == "")
            {
                $category = find_category_by_path($articles, $path);
            }
            
            // Cat exists
            if(in_array($category, $categories))
            {
                // Find article metadata if it was most recent.
                if($category == "most-recent")
                {
                    $category = find_category_by_path($articles, $path);
                    
                }
                
                // If not found try find it by path.
                // Category might have changed.
                if(!isset($articles[$category][$path]))
                {
                    $category = find_category_by_path($articles, $path);
                }
                
                // Article in right category.
                if(isset($articles[$category][$path]))
                {
                    // Prevent LFI / RFI.
                    if(preg_match("/^[a-zA-Z0-9_-]+$/", $path) == 1)
                    {
                        // Get article meta data.
                        $smart = $path;
                        $metadata = $articles[$category][$path];
                        $elapsed = time() - ((int) $metadata[1]);
                        $n_years = 31536000 * 2;
                        $article_title_s = htmlspecialchars($metadata[0]);
                        $date_str = date("j M, Y", $metadata[1]);
                        $date_str_s = htmlspecialchars($date_str);
                        $page_title = $date_str_s . " - $article_title_s";
                        
                        // Open file.
                        $sep = DIRECTORY_SEPARATOR;
                        $path = join($sep, array("articles", $path));
                        if(file_exists($path))
                        {
                            $md_path = join($sep, array($path, "README.md"));
                            $home_path = join($sep, array($path, "index.php"));
                            
                            // Parse markdown.
                            if(file_exists($md_path))
                            {
                                $markdown = file_get_contents($md_path);
                                $Parsedown = new Parsedown();
                                $content = "<div class='article_content'>";
                                //$content .= "<div class='article_date'>";
                                //$content .= "$date_str_s</div>";
                                if($elapsed >= $n_years)
                                {
                                    $content .= "<p><b>Note:</b> this article is now at least two years old and may contain serious errors.</p>";
                                }
                                $content .= "<h1 class='article_title'>";
                                $content .= "$article_title_s</h1>";
                                $content .= $Parsedown->text($markdown);
                                $content .= "</div>";
                            }
                            else
                            {
                                include "$home_path";
                            }
                            
                            break;
                        }
                    }
                }
            }
            
            // If checks above don't pass it drops into displaying home page.
        default:
            $left_col = article_list_col("left", $categories, $articles);
            $right_col = article_list_col("right", $categories, $articles);
            $content = "
                <div class='about-blurb'><div style='color: #FFD700; padding-bottom: 6px; clear:both;'>Hi, I'm <b>Matthew Roberts</b>: a blockchain engineer and researcher.</div>Here I publish my sekrit research and codez exploring a number of topics. I am particularly interested in digital asset systems that feature novel, useful, and verifiable work functions; smart contacts that reduce trust in online transactions; and decentralised trading systems. Though I have also worked on many other projects such as P2P network stacks, smart supply chain protocols, mobile & SIM card authentication, trusted computing, P2P file storage, marketplace systems, and much more.</div>
                <!-- <div class='about-spacer'></div> -->
                <table class='articles-list-table'>
                    <tr>
                        <td>$left_col</td>
                        <td>$right_col</td>
                    </tr>
                </table>
            ";
            break;
    }
    
    // Build footer.
    global $bk1_base;
    global $bk2_base;
    $bk1 = $bk1_base . $path;
    $bk2 = $bk2_base . $smart;
    $footer  = "<a href='$bk1'>Backup 1</a>&nbsp;&nbsp;&nbsp;•&nbsp;&nbsp;&nbsp;";
    //$footer .= "<a href='$bk2'>Backup 2</a>&nbsp;&nbsp;&nbsp;•&nbsp;&nbsp;&nbsp;";
    if(strlen($date_str_s))
    {
        $footer .= "Published " . $date_str_s;
    }
    else
    {
        $footer .= "You can disable Javascript for this website";
    }
    $footer .= "&nbsp;&nbsp;&nbsp;•&nbsp;&nbsp;&nbsp;matthew [et] roberts [dawt] pm";
    
    // Rest of page metadata.
    $content = isset($content) ? $content : "Content not set.";    
    $page_info = array(
        "page_title" => $page_title,
        "main_content" => $content,
        "footer" => $footer
    );
    
    return $page_info;
}

$page = isset($_GET["p"]) ? $_GET["p"] : "home";
$articles["most-recent"] = most_recent_articles($categories, $articles, 5);
$page_info = load_page($page, $categories, $articles, $page_title);
$css = file_get_contents("site.css");
header('Content-type: text/html; charset=utf-8');
?>
<html>
<head>
    <title><?php echo $page_info["page_title"]; ?></title>
    <link rel="shortcut icon" href="data:image/x-icon;base64,AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAme///5nv//9D4v//AFNi/wAjKv8AIyr/ACMq/wAjKv8ADA//AAwP/wBTYv9D4v//me///5nv//8AAAAAAAAAAN36//+Z7///AFNi/wAjKv8AU2L/AFNi/wBTYv8AIyr/ACMq/wAMD/8ADA//AFNi/5nv///d+v//AAAAAAAAAADd+v//3fr//wAjKv8AIyr/AFNi/0Pi//8AU2L/AFNi/wAjKv8AIyr/ACMq/wAMD//d+v//3fr//wAAAAAAAAAAAAAAAN36//8ADA//ACMq/wBTYv8AwOT/Q+L//wBTYv8AU2L/ACMq/wAjKv8ADA//3fr//wAAAAAAAAAAAAAAAAAAAAAAAAAAQ+L//wAjKv8AU2L/AMDk/0Pi//8AU2L/AFNi/wBTYv8AIyr/Q+L//wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAN36//8ADA//ACMq/wDA5P8AwOT/AFNi/wBTYv8AIyr/AAwP/936//8AAAAAAAAAAAAAAABA3/wAPtn2AABTYgcAAAAAQ+L//wAjKv8AU2L/AMDk/wDA5P8AU2L/ACMq/0Pi//8AAAAAAAAAAAAAAAAAAAAAQd/8ADrS7wAos9ACBX2UCt36//8AU2L/AFNi/wBTYv8AwOT/AMDk/wAjKv/d+v/mAAAAAABTYgUAU2IAAAAAAD3a9wA20O0AK7zZAAKMpgkAAAAAAFNi////////////3fr//936//8AU2L/AAAAAEPi/wUAwOQAAMDkAABTYgA82PUANc7rACu92wAEob8FAAAAAJnv///d+v///////936///d+v//me///wAAAAAAwOQB////AADA5AAAU2IAO9j1ADfR7gAvxeIAQ67KAgAAAAAAAAAAme/////////d+v//me///wAAAAAAAAAAAAAAAADA5AAAwOQAAAAAADrX9AA20O0AMMbjAESxzQEAAAAAAAAAAN36////////3fr//936//8AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA51fIANc7sAC/H5QE5q8gBAAAAAAAAAADd+v///////936///d+v//AAAAAAAAAAAAAAAAQ+L/AAAAAAAAAAAAOdXxADTO6wAvx+QBPa/LAQAAAADd+v//3fr////////d+v//3fr//936//8AAAAAAAAAAAAAAAAAAAAAAAAAADnV8gA1zusAMcjmAgAAAAAAAAAAme///936////////3fr//936//+Z7///AAAAAAAAAAAAAAAAAAAAAEPi/wI61vMDN9HuAzTN6wkAAAAAAAAAAAAAAACZ7///3fr//936//+Z7///AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAgAEAAIABAACAAQAAwAMAAOAHAADgBwAA0A8AAMALAADoFwAA6BcAAOw/AADsPwAAzD8AAMgfAADYHgAAHD8AAA==" />
    <style type="text/css">
<?php echo $css; ?>    
    </style>
</head>
<body>
<!-- This is my website. There are many like it, but this one is mine. -->
<center>
<div class="nav-div">
    <div class="nav-div-wrapper">
        <a href="index.php">Home</a><div class="link-spacer">|</div><a href="index.php?p=article&path=work&category=philosophy">Work</a>
    </div>
</div>
<div class="main-div">
<center>
<?php echo $page_info["main_content"]; ?>
</center>
</div>
<div class="footer-div">
<center>
<?php echo $page_info["footer"]; ?>
</center>
</div>
</center>
</body>
</html>


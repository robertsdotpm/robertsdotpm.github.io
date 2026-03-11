<?php
include "config.php";

function article_list($category, $articles)
{    
    $html = "<ul>";
    foreach($articles[$category] as $pretty_url => $meta_data)
    {
        $title_s = htmlspecialchars($meta_data[0]);
        $category_s = urlencode($category);
        $path_s = urlencode($pretty_url);
        $href_s = "index.php?p=article&path={$path_s}&category={$category_s}";
        $html .= "<li><a href='$href_s'>$title_s</a></li>";
    }
    $html .= "</ul>";
    
    return $html;
}

function most_recent_articles($categories, $articles, $limit)
{
    // Setup initial list to sort.
    $sorted_descending = array();
    foreach($categories as $category)
    {
        // Articles in specific category.
        foreach($articles[$category] as $pretty_url => $meta_data)
        {
            $sorted_descending[$pretty_url] = $meta_data;
            continue;
        }
    }
        
    // Keep doing bubble sort until there's nothing left to sort.
    $not_sorted = True;
    while($not_sorted)
    {
        // Optimistic assumption.
        $not_sorted = False;
        
        // Loop over articles to sort.
        $l = count($sorted_descending);
        for($i = 0; $i < $l; $i++)
        {
            if($i == $l - 1) break;
            
            $left = array_slice($sorted_descending, $i, 1, true);
            $right = array_slice($sorted_descending, $i + 1, 1, true);
            
            $start = array_slice($sorted_descending, 0, $i + 2, true);
            if($i + 2 < $l)
            {
                $stop = array_slice($sorted_descending, $i + 2, count($sorted_descending) - ($i + 1), true);
            }
            else
            {
                $stop = array();
            }
            
            // Now we know it's still not sorted.
            //echo (array_values($left)[0])[1];
            //echo "<br>";
            //echo (array_values($right)[0])[1];
            //echo "<br>";
            
            $x = array_values($left);
            $y = array_values($right);
            if($x[0][1] < $y[0][1])
            {
                //echo "yes";
                
                // Remove current elements.
                array_pop($start);
                array_pop($start);
                
                // Add them back in new order.
                $start[key($right)] = array_values($right)[0];
                $start[key($left)] = array_values($left)[0];
                
                // Replace current array with sorted one.
                $sorted_descending = array_merge($start, $stop);
                
                // Keep on sorting.
                $not_sorted = True;
                break;
            }
        }
    }
    
    return array_slice($sorted_descending, 0, $limit, true);
}

function article_list_cell($pair_offset, $categories, $articles)
{
    $heading_type = "regular";
    $category = $categories[$pair_offset];
    if($category == "most-recent")
    {
        $heading_type = "recent";
        $articles["most-recent"] = most_recent_articles($categories, $articles, 5);
    }
    
    $article_list = article_list($category, $articles);
    $html = "
        <font class='category-heading-$heading_type'>$category</font>
        <div class='spacer'>&nbsp;</div>
        <center>
            <div class='articles-list-div'>$article_list</div>
        </center>
        <div class='category-spacer'>&nbsp;</div>
    ";
    
    return $html;
}

function article_list_col($col_type, $categories, $articles)
{
    $html = "";
    for($i = 0; $i < (int) ceil(count($categories) / 2); $i++)
    {
        // Left cell.
        $pair_offset = $i * 2;
        if($col_type == "left")
        {
            $html .= article_list_cell($pair_offset, $categories, $articles);
        }
        
        // Right cell and avoid overflow.
        if($col_type == "right")
        {
            if($pair_offset + 1 < count($categories))
            {
                $pair_offset++;
                $html .= article_list_cell($pair_offset, $categories, $articles);
            }
            else
            {
                // Otherwise blank.
                $html .= "<td>&nbsp;</td>";
            }
        }
    }

    return $html;
}

function find_category_by_path($articles, $path)
{
    foreach($articles as $category => $articles_list)
    {
        // Articles in specific category.
        foreach($articles[$category] as $pretty_url => $meta_data)
        {
            if($pretty_url == $path)
            {
                return $category;
            }
        }
    }
    
    return "";
}


?>
<?php
// Purpose: This code searches the database for all categories; results are displayed in a multi-column list to the user

// Set Title, Description, and Keywords
$pageTitle = 'MSU Dataset Search Categories List';
$pageDescription = 'List of categories from the MSU Dataset Search database.';
$pageKeywords = 'MSU, data';

// Select page layout - choices include: no columns = fullWidth, and right column = rightCol
$pageLayout = 'fullWidth';

// Get database parameters and connect to database
include_once './meta/assets/dbconnect.inc';

// Include page header
include './meta/inc/header.php';
?>

<div id="main">
	<a name="mainContent"></a>
	<div class="gutter">
		<h2 class="mainHeading">Alphabetical List of Categories <span class="controls"><a class="expand" href="./categories.php" title="View categories arranged in a numbered weighted list">list view</a> | <a class="expand" href="./categories-cloud.php" title="View categories arranged in a weighted cloud list">cloud view</a></span></h2>
    	<ul id="listColumns">

<?php
			// Get categories
			$query = "
				SELECT category
				FROM (
					SELECT recordInfo_recordIdentifier, replace(dataset_category1, '\"', '') AS category
					FROM datasets
					WHERE status = 'a'
					GROUP BY recordInfo_recordIdentifier
				UNION
					SELECT recordInfo_recordIdentifier, replace(dataset_category2, '\"', '') AS category
					FROM datasets
					WHERE status = 'a'
					GROUP BY recordInfo_recordIdentifier
				UNION
					SELECT recordInfo_recordIdentifier, replace(dataset_category3, '\"', '') AS category
					FROM datasets
					WHERE status = 'a'
					GROUP BY recordInfo_recordIdentifier
				) AS categories
				GROUP BY categories.category
				ORDER BY categories.category ASC
			";
	
			// Place query result in variable
			$getCategories = mysql_query($query);
    
			// Setup while loop, and format for display from $getCategories query
			while ($row = mysql_fetch_object($getCategories)) {
				$category = $row->category;

            /**
             *  Faster count, but not as accurate.
             *
             *  In the $getCategories query, do:
             *      SELECT category, count( recordInfo_recordIdentifier ) AS category_count
             *
             *  Then, here:
             *      $categoryCount = $row->category_count;
             */

				// Get count of this category
                $query = "
                    SELECT COUNT(*) AS matchCount
                    FROM datasets
                    WHERE status = 'a'
                    AND MATCH (dataset_category1, dataset_category2, dataset_category3) AGAINST ('\"$category\"' IN BOOLEAN MODE)
                ";
                $countResult = mysql_query($query);
                $count = mysql_fetch_assoc($countResult);
                $categoryCount = $count['matchCount'];

                // Display selected record/item(s) to the user
                if ($category != "") {
                    echo "<li><a href='./search.php?category=\"" . urlencode($category) . "\"'>$category ($categoryCount article" .
                         (($categoryCount == 1) ? "" : "s") . ")</a></li>\n";
                }
    		}
?>

		</ul><!-- end listColumns -->
		<p class="nav return"><a class="bck" href="./index.php">Back to search page</a></p>
	</div><!-- end gutter div -->
</div><!-- end main div -->

<?php
include './meta/inc/footer.php';
?>

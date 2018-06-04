phpOLAPi - A PHP API for XMLA to connect to OLAP databases
======

phpOLAPi is a successor of [phpOLAP](https://github.com/julienj/phpOlap) by Julien Jacottet, which is unfortunately not maintained anymore. 

phpOLAPi can be used to explore database schemas (cubes, dimensions, hierarchies, levels, ...), execute MDX Queries and render the results in various forms: as a PHP array, a CSV text or an HTML table.

Install
-----

```
composer require kabachello/phpolapi
```

phpOLAPi runs on PHP 5.3.2 and up.

Connect
-----

``` php
<?php
require_once 'vendor/autoload.php';
use phpOLAPi\Xmla\Connection\Connection;
use phpOLAPi\Xmla\Connection\Adaptator\SoapAdaptator;

// for Mondrian
$connection = new Connection(
    new SoapAdaptator('http://localhost:8080/mondrian/xmla'),
    array(
            'DataSourceInfo' => 'Provider=Mondrian;DataSource=MondrianFoodMart;'
            'CatalogName' => 'FoodMart',
            'schemaName' => 'FoodMart'
        )
);

// for Microsoft SQL Server Analysis Services

$connection = new Connection(
    new SoapAdaptator('http://localhost/olap/msmdpump.dll', 'username', 'password'),
    array(
        'DataSourceInfo' => null,
        'CatalogName' => 'Adventure Works DW 2008R2 SE'
        )
);
```

Run an MDX query
-----

``` php
$connection = ...

$resultSet = $connection->statement("
	SELECT [Measures].MEMBERS ON COLUMNS FROM [Adventure Works] 
");

echo $resultSet;
```

Build an MDX query via API
-----

``` php
use phpOLAPi\Mdx\Query;

$query = new Query("[Sales]");
$query->addElement("[Measures].[Unit Sales]", "COL");
$query->addElement("[Measures].[Store Cost]", "COL");
$query->addElement("[Measures].[Store Sales]", "COL");
$query->addElement("[Gender].[All Gender].Children", "COL");
$query->addElement("[Promotion Media].[All Media]", "ROW");
$query->addElement("[Product].[All Products].[Drink].[Alcoholic Beverages]", "ROW");
$query->addElement("[Promotion Media].[All Media].Children", "ROW");
$query->addElement("[Product].[All Products]", "ROW");
$query->addElement("[Time].[1997]", "FILTER");

$connection = ...

$resultSet = $connection->statement(
	$query->toMdx()
);
```

Render the ResultSet
------

The result of a query is a ResultSet, which mimics the (very complex) structure of an XMLA response. Renderers help extract the actual data, which is burried deep in the XML. 

``` php
use phpOLAPi\Renderer\Table\HtmlTableRenderer;
use phpOLAPi\Renderer\Table\CsvTableRenderer;
use phpOLAPi\Renderer\AssocArrayRenderer

$connection = ...

$resultSet = $connection->statement("
	SELECT	
		{ 
			[Measures].[internet Sales Amount],
			[Measures].[Internet Order Quantity] 
		} ON COLUMNS,
		{
			[Date].[Calendar].[Calendar Year].[CY 2006],
			[Date]. [Calendar].[Calendar Year].[CY 2007] 
		} ON ROWS
	FROM
	    [Adventure Works]
	WHERE
	    ([Customer].[Customer Geography].[Country].[Australia])

");

// Associative array (similar to the result of SQL queries)
$array = (new AssocArrayRenderer($resultSet))->generate();
var_dump($array);

// HTML table
$tableRenderer = new HtmlTableRenderer($resultSet);
echo $tableRenderer->generate();

// CSV
header("Content-type: application/vnd.ms-excel"); 
header("Content-disposition: attachment; filename=\"export.csv\"");
$csv = new CsvTableLayout($resultSet);
print($csv->generate()); 
```

Database exploration
----------------

``` php
<?php

$connection = ...

$cube = $connection->findOneCube(null, array('CUBE_NAME' => 'Sales'));
	
?>


<p><label>Cube :</label> <?php echo $cube->getName() ?></p>
<ul id="cubeExploration">
	<li class="measure">
		Measures
		<ul>
			<?php foreach ($cube->getMeasures() as $measure): ?>
				<li><?php echo $measure->getCaption() ?></li>
			<?php endforeach ?>
		</ul>
	</li>		
	<?php foreach ($cube->getDimensionsAndHierarchiesAndLevels() as $dimention): ?>
		<?php if($dimention->getType() != 'MEASURE') : ?>
		<li>
			<?php echo $dimention->getCaption() ?>
			<ul>
				<?php foreach ($dimention->getHierarchies() as $hierarchy): ?>
					<li>
						<?php echo $hierarchy->getCaption() ?>
						<ul>
							<?php foreach ($hierarchy->getLevels() as $level): ?>
								<li>
									<?php echo $level->getCaption() ?>
								</li>
							<?php endforeach ?>
						</ul>
					</li>
				<?php endforeach ?>
			</ul>
		</li>
		<?php endif; ?>
	<?php endforeach ?>
</ul>
		
```

License
-------

phpOLAPi is released under the MIT license.

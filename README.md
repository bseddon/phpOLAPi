phpOLAPi - A PHP API for XMLA to connect to OLAP databases
======


phpOLAPi is a successor of [phpOLAP](https://github.com/julienj/phpOlap) by Julien Jacottet, which is unfortunately not maintained anymore. 

phpOLAPi can be used to explore database schemas (cubes, dimensions, hierarchies, levels, ...), execute MDX Queries and render the results in various forms: as a PHP array, a CSV text or an HTML table.

phpOLAPi runs on PHP 5.3.2 and up.

Connect
-----

Database exploration
----------------

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
/*
$connection = new Connection(
    new SoapAdaptator('http://192.168.1.12/olap/msmdpump.dll', 'julien', 'juju'),
    array(
        'DataSourceInfo' => null,
        'CatalogName' => 'Adventure Works DW 2008R2 SE'
        )
);
*/
```

Perform an MDX query
-----

``` php
<?php

require_once 'vendor/autoload.php';

use phpOLAPi\Mdx\Query;

$resultSet = $connection->statement("
	SELECT ...
");

echo $resultSet;
```

Build and MDX query via API
-----

``` php
<?php

require_once 'vendor/autoload.php';

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

echo $query->toMdx();
```

Render the result
------
``` php
<?php
require_once 'vendor/autoload.php';

use phpOLAPi\Xmla\Connection\Connection;
use phpOLAPi\Xmla\Connection\Adaptator\SoapAdaptator;
use phpOLAPi\Renderer\Table\HtmlTableRenderer;
use phpOLAPi\Renderer\Table\CsvTableRenderer;
use phpOLAPi\Renderer\AssocArrayRenderer

$connection = ...

$resultSet = $connection->statement("
	select Hierarchize(Union(Union({([Measures].[Unit Sales], [Gender].[All Gender], [Marital Status].[All Marital Status])}, Union(Union(Crossjoin({[Measures].[Store Cost]}, {([Gender].[All Gender], [Marital Status].[All Marital Status])}), Crossjoin({[Measures].[Store Cost]}, Crossjoin([Gender].[All Gender].Children, {[Marital Status].[All Marital Status]}))), Crossjoin({[Measures].[Store Cost]}, Crossjoin({[Gender].[F]}, [Marital Status].[All Marital Status].Children)))), Crossjoin({[Measures].[Store Sales]}, Union(Crossjoin({[Gender].[All Gender]}, {[Marital Status].[All Marital Status]}), Crossjoin({[Gender].[All Gender]}, [Marital Status].[All Marital Status].Children))))) ON COLUMNS,
	  Crossjoin(Hierarchize(Crossjoin(Union({[Promotion Media].[All Media]}, [Promotion Media].[All Media].Children), Union(Union({[Product].[All Products]}, [Product].[All Products].Children), [Product].[Food].Children))), {[Store].[All Stores]}) ON ROWS
	from [Sales]
	where {[Time].[1997]}

");

// Associative array
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
exit;

Database exploration
----------------

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
/*
$connection = new Connection(
    new SoapAdaptator('http://192.168.1.12/olap/msmdpump.dll', 'julien', 'juju'),
    array(
        'DataSourceInfo' => null,
        'CatalogName' => 'Adventure Works DW 2008R2 SE'
        )
);
*/

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

<?php
namespace phpOLAPi\Renderer;

use phpOLAPi\Metadata\ResultSetInterface;

/**
 *
 *
 * @author Andrej Kabachnik
 *
 */
class AssocArrayRenderer implements RendererInterface
{
    private $resultSet = null;
    
    public function __construct(ResultSetInterface $resultSet)
    {
        $this->resultSet = $resultSet;
    }
    
    public function generate()
    {
        $table = [];
        $resultSet = $this->resultSet;
        
        $rowAxisSet = $resultSet->getRowAxisSet();
        $dataSet = $resultSet->getDataSet();
        
        // Get the keys of the table columns
        $keys = $this->getColumnKeys($resultSet);
        
        // Now fill the table
        if (! empty($rowAxisSet)) {
            // If there are row axes defined, loop through them
            foreach($rowAxisSet as $row => $aCol) {
                $rowContent = [];
                
                // Row axis data
                foreach ($aCol as $col => $oCol) {
                    $rowContent[] = $rowAxisSet[$row][$col]->getMemberCaption();
                }
                
                // Cell data (columns)
                $rowNum = count($cols);
                $start =  $rowNum * $row;
                $stop = $start + $rowNum;
                for ($i=$start; $i < $stop; $i++) {
                    if (isset($dataSet[$i])) {
                        $rowContent[] = $dataSet[$i]->getValue();
                    } else {
                        $rowContent[] = '';
                    }
                }
                
                // Create a table row by using the keys for keys and content for values
                $table[$row] = array_combine($keys, $rowContent);
            }
        } else {
            // If there are no row axes, just use the data to populate a single row
            $colNrs = array_keys($resultSet->getColAxisSet());
            foreach ($colNrs as $colNr) {
                $data = $dataSet[$colNr];
                $rowContent[$keys[$colNr]] = ($data === null ? null : $data->getValue());
            }
            $table[] = $rowContent;
        }
        return $table;
    }
    
    /**
     * Returns an array with column keys for the table: keys of the row axes followed by the columns axes.
     * 
     * @param ResultSetInterface $resultSet
     * @return string[]
     */
    protected function getColumnKeys(ResultSetInterface $resultSet)
    {
        $keys = [];
        
        $rowAxisSet = $resultSet->getRowAxisSet();
        $rowAxisCols = [];
        if (is_array($rowAxisSet)) {
            foreach ($rowAxisSet as $rowAxis) {
                foreach ($rowAxis as $axis) {
                    $rowAxisCols[$axis->getLevelUniqueName()] = $axis->getLevelUniqueName();
                }
            }
            
            $keys = array_values($rowAxisCols);
        }
        
        $cols = $resultSet->getColAxisSet();
        if (is_array($cols)) {
            foreach ($cols as $colAxis) {
                foreach ($colAxis as $axis) {
                    $keys[] = $axis->getMemberUniqueName();
                }
            }
        }
        
        return $keys;
    }
}
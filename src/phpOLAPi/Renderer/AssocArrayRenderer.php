<?php
namespace phpOLAPi\Renderer;

use phpOLAPi\Metadata\ResultSetInterface;

/**
 * 
 * 
 * @author kabachello
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
        
        $rowAxisCols = [];
        foreach ($resultSet->getRowAxisSet() as $rowAxis) {
            foreach ($rowAxis as $axis) {
                $rowAxisCols[$axis->getLevelUniqueName()] = $axis->getLevelUniqueName();
            }
        }
        $keys = array_values($rowAxisCols);
        foreach ($resultSet->getColAxisSet() as $colAxis) {
            foreach ($colAxis as $axis) {
                $keys[] = $axis->getMemberCaption();
            }
        }
        
        foreach($rowAxisSet as $row => $aCol)
        {
            $rowContent = [];
            
            // Axis cells
            foreach ($aCol as $col => $oCol) {
                $rowContent[] = $rowAxisSet[$row][$col]->getMemberCaption();
            }
            
            // Datas
            $rowNum = count($resultSet->getColAxisSet());
            $start =  $rowNum * $row;
            $stop = $start + $rowNum;
            for ($i=$start; $i < $stop; $i++) {
                if (isset($dataSet[$i])) {
                    $rowContent[] = $dataSet[$i]->getValue();
                } else {
                    $rowContent[] = '';
                }
            }
            
            $table[$row] = array_combine($keys, $rowContent);
        }
        return $table;
    }
}
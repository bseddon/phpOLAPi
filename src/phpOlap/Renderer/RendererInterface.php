<?php 

/*
* This file is part of phpOlap.
*
* (c) Julien Jacottet <jjacottet@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace phpOLAPi\Renderer;

use phpOLAPi\Metadata\ResultSetInterface;

/**
*	Renderer Interface
*
*  	@author Julien Jacottet <jjacottet@gmail.com>
*	@package Renderer
*/

interface RendererInterface {
	
    /**
     * Constructor.
     *
     * @param ResultSetInterface $resultSet The resultSet object
     *
     */	
	public function __construct (ResultSetInterface $resultSet);
	

    /**
     * generate the layout
     *
     * @return String
     *
     */
	public function generate();

}
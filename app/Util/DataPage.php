<?php

namespace TATravel\Util;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DataPage
 *
 * @author AsusPC
 */
class DataPage {    
    private $totalData;
    private $totalPage;
    private $currentPage;
    private $nextPage;
        
    public function setTotalData($totalData){
        $this->totalData = $totalData;
        return $this;
    }
    
    public function setTotalPage($totalPage){
        $this->totalPage = $totalPage;
        return $this;
    }
        
    public function setCurrentPage($currentPage){
        $this->currentPage = $currentPage;
        return $this;
    }
    
    public function setNextPage($nextPage){
        $this->nextPage = $nextPage;
        return $this;
    }
    
    public function get(){
        $dataPage['totalData'] = $this->totalData;
        $dataPage['totalPage'] = $this->totalPage;
        $dataPage['currentPage'] = $this->currentPage;
        $dataPage['nextPage'] = $this->nextPage;
        
        return $dataPage;
    }
}

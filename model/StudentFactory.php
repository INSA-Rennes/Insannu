<?php
class StudentFactory {
  private $list;

  public function StudentFactory() {
    $this->list = [];
  }

  public function search($keywords) {
    $app = Main::getInstance()->getApp();

    $keywordsArray = explode(' ', $keywords);

    $sql = "SELECT * FROM students WHERE 1=1 ";
    $params = [];

    foreach ($keywordsArray as $keyword) {
      //ROOM - like BNC205
      if (preg_match('/^[ABCD]{1}[NS]{1}[NCS]{0,1}[0-9]{3}$/i',$keyword)) {
        if($keyword[2] == 'N' || $keyword[2] == 'C' || $keyword[2] == 'S') {
          $keyword = substr_replace($keyword,'',2,1);  
        }
        $sql .= "AND room LIKE ? ";
        $params[] = $keyword;
        error_log("Match a room : ".$keyword);

      } 

      //CLASS - like 2STPI
      else if (preg_match('/^([1-5]{1})(stpi|info|gcu|gma|sgm|eii|src|arom)$/i', $keyword, $res)) {
        $year = $res[1];
        $depart = $res[2];
        $sql .= "AND groupe LIKE ? AND department LIKE ? ";
        $params[] = $res[1]; $params[] = $res[2];
        error_log("Match a class : ".$keyword); 
      } 

      //NAME
      else {
        $sql .= "AND (last_name LIKE ? OR first_name LIKE ? OR login LIKE ?) ";
        array_push($params, '%'.$keyword.'%', '%'.$keyword.'%', '%'.$keyword.'%');

        error_log("Match a name : ".$keyword);
      }
    }

    $req = $app['db']->executeQuery($sql, $params);
    $this->loadFromDB($req->fetchAll());
  }

  private function loadFromDB($pl) {
    foreach ($pl as $u) {
      $s = new Student();
      $s->loadFromDB($u);
      $this->list[] = $s;
    }
  }

  public function getJSON() {
    return json_encode($this->list);
  }
}

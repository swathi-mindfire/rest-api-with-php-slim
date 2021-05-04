<?php

  function sanitizeData($data) 
  {
      $data = trim($data);
      $data = stripslashes($data);
      $data = htmlspecialchars($data,ENT_QUOTES);

      return $data;
  }

?>
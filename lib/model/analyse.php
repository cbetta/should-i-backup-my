<?php
/**
* 
*/
class Analyse 
{
  public static function token($token)
  {
    $output = array();
    $output['backup_links'] = Analyse::backup($token);
    $output['news_links'] = Analyse::news($token);
    $output['state'] = Analyse::state($token);
    
    
    return $output;
  }
  
  public static function backup($token)
  {
    $url = "http://query.yahooapis.com/v1/public/yql?q=select%20title%2C%20url%20from%20search.web%20where%20query%3D%22".urlencode($token)."%20backup%22%20limit%205&format=json";
 
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    $output = curl_exec($ch); 
    curl_close($ch);    
    
    $data = json_decode($output);
    
    return $data->query->results->result;
  }
  
  public static function news($token)
  {
    $url = "http://api.guardianapis.com/content/search?q=".urlencode($token)."&format=json&api_key=wx2ttpgmacxjzmvr3se4gqky";
  
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    $output = curl_exec($ch); 
    curl_close($ch);    
    
    $data = json_decode($output);
    $output = $data->search->results;
    
    foreach ($output as $article) {
      $article->headline = str_replace($token, '<strong>'.$token.'</strong>', $article->headline);
    }
    
    return $output;
  }
  
  public static function state($token) {
    $url1 = "http://api.guardianapis.com/content/search?q=".urlencode($token." profit")."&format=json&api_key=wx2ttpgmacxjzmvr3se4gqky&after=20090101";
    $url2 = "http://api.guardianapis.com/content/search?q=".urlencode($token." loss")."&format=json&api_key=wx2ttpgmacxjzmvr3se4gqky&after=20090101";
        
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $url1); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    $output1 = curl_exec($ch); 
    curl_close($ch);    

    $data1 = json_decode($output1);
    
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $url2); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    $output2 = curl_exec($ch); 
    curl_close($ch);    

    $data2 = json_decode($output2);
    
    $profit = (int) $data1->search->count;
    $loss =  (int) $data2->search->count;
    
    if ($profit > 1.2*$loss) return "Not really, but why not";
    elseif ($profit > $loss) return "Probably Not";
    elseif ($profit <= $loss) return "Probably";
    elseif ($profit*1.2 < $loss) return "Yes, now!";
    
  }
}

?>
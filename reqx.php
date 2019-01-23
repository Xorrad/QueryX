<?php
function reqx($req, $arguments = array(), $debug = false)
{
	global $bdd;
	
	$req_split = explode(" ", $req);
	$methode = $req_split[0];
	$cancel_execution = false;
	
	switch($methode)
	{
		case 'SELECT':
			if($debug)
			{
				$table = $req_split[3];
				
				if(!table_exists($table))
				{
					trigger_error('table "'.$table.'" not exists in table');
					exit();
				}
				
				$colomns = explode(",", $req_split[1]);
				foreach($colomns as $colomn)
				{
					if(!colomn_exists($table, $colomn))
					{
						if($colomn == '*')
						{
							break;
						}
						
						trigger_error('colomn "'.$colomn.'" not exists in table "'.$table.'"');
						$cancel_execution = true;
					}
				}
				
				$wheres = array();
				$is_where_bloc = false;
				$i = 0;
				foreach($req_split as $bloc)
				{
					if(!$is_where_bloc)
					{
						if($bloc == "WHERE")
						{
							$is_where_bloc = true;
						}
					}
					else
					{
						if($bloc == '?')
						{
							if(count($req_split) != $i+1)
							{
								if($req_split[$i+1] != 'AND')
								{
									break;
								}
							}
							else
							{
								break;
							}
						}
						else
						{
							if($bloc != '=' && $bloc != '?' && $bloc != 'AND')
							{
								array_push($wheres, $bloc);
							}
						}
					}
					
					$i++;
				}
				foreach($wheres as $where)
				{
					if(!colomn_exists($table, $where))
					{
						trigger_error('colomn "'.$colomn.'" not exists in table "'.$table.'"');
						$cancel_execution = true;
					}
				}
				
				if($cancel_execution)
				{
					exit();
				}
			}
		
			$req = $bdd->prepare($req);
			$req->execute($arguments);
			$rowCount = $req->rowCount();
			$resultats = array();
			
			while($donnees = $req->fetch(PDO::FETCH_ASSOC))
			{
				if($rowCount <= 1)
				{
					$req->closeCursor();
					return(array($rowCount, $donnees));
				}
				else
				{
					array_push($resultats, $donnees);
				}
			}
			$req->closeCursor();
			return(array($rowCount, $resultats));
		break;
		case 'UPDATE':
			if($debug)
			{
				$table = $req_split[1];
				
				if(!table_exists($table))
				{
					trigger_error('table "'.$table.'" not exists in table');
					exit();
				}
				
				$colomns = array();
				$is_colomn_bloc = false;
				$i = 0;
				foreach($req_split as $bloc)
				{
					if(!$is_colomn_bloc)
					{
						if($bloc == "SET")
						{
							$is_colomn_bloc = true;
						}
					}
					else
					{
						if($bloc == '?')
						{
							if(count($req_split) != $i+1)
							{
								if($req_split[$i+1] != '?,' || $req_split[$i+1] != ',')
								{
									break;
								}
							}
							else
							{
								break;
							}
						}
						else
						{
							if($bloc != '=' && $bloc != '?' && $bloc != '?,' && $bloc != ',')
							{
								if($bloc == '*')
								{
									break;
								}
								array_push($colomns, $bloc);
							}
						}
					}
					
					$i++;
				}
				foreach($colomns as $colomn)
				{
					if(!colomn_exists($table, $colomn))
					{
						trigger_error('colomn "'.$colomn.'" not exists in table "'.$table.'"');
						$cancel_execution = true;
					}
				}
				
				$wheres = array();
				$is_where_bloc = false;
				$i = 0;
				foreach($req_split as $bloc)
				{
					if(!$is_where_bloc)
					{
						if($bloc == "WHERE")
						{
							$is_where_bloc = true;
						}
					}
					else
					{
						if($bloc == '?')
						{
							if(count($req_split) != $i+1)
							{
								if($req_split[$i+1] != 'AND')
								{
									break;
								}
							}
							else
							{
								break;
							}
						}
						else
						{
							if($bloc != '=' && $bloc != '?' && $bloc != 'AND')
							{
								array_push($wheres, $bloc);
							}
						}
					}
					
					$i++;
				}
				foreach($wheres as $where)
				{
					if(!colomn_exists($table, $where))
					{
						trigger_error('colomn "'.$colomn.'" not exists in table "'.$table.'"');
						$cancel_execution = true;
					}
				}
				
				if($cancel_execution)
				{
					exit();
				}
			}
			
			$req = $bdd->prepare($req);
			$req->execute($arguments);
			$req->closeCursor();
			return $req->fetch();
		break;
		case 'INSERT':
			if($debug)
			{
				$splited_table = explode("(", $req_split[2]);
				$table = $splited_table[0];
				
				if(!table_exists($table))
				{
					trigger_error('table "'.$table.'" not exists in table');
					exit();
				}
				
				$colomns = array();
				$is_colomn_bloc = false;
				$i = 0;
				foreach($req_split as $bloc)
				{
					if(!$is_colomn_bloc)
					{
						$splited_bloc = explode("(", $bloc);
						if(count($splited_bloc)>=2)
						{
							$is_colomn_bloc = true;
							$final_bloc = explode(",", $splited_bloc[1]);//Remove the ','
							array_push($colomns, $final_bloc[0]);
						}
					}
					else
					{
						$splited_bloc = explode(")", $bloc);
						if(count($splited_bloc)>=2)
						{
							array_push($colomns, $splited_bloc[0]);
							break;
						}
						else
						{
							if($bloc != ',')
							{
								$splited_bloc = explode(",", $bloc);//Remove the ','
								array_push($colomns, $splited_bloc[0]);
							}
						}
					}
					
					$i++;
				}
			
				foreach($colomns as $colomn)
				{
					if(!colomn_exists($table, $colomn))
					{
						trigger_error('colomn "'.$colomn.'" not exists in table "'.$table.'"');
						$cancel_execution = true;
					}
				}
				
				if($cancel_execution)
				{
					exit();
				}
			}
			
			$req = $bdd->prepare($req);
			$req->execute($arguments);
			$req->closeCursor();
			return $req->fetch();
		break;
		default:
			$req = $bdd->prepare($req);
			$req->execute($arguments);
			$req->closeCursor();
			return $req->fetch();
		break;
	}
}

function colomn_exists($table, $colomn)
{
	global $bdd;
	$req =	$bdd->prepare("SHOW COLUMNS FROM `$table` LIKE '$colomn'");
	$req->execute();
	$rowCount = $req->rowCount();
	$req->closeCursor();
	return ($rowCount > 0) ? true : false;
}

function table_exists($table)
{
	global $bdd;
	$req =	$bdd->prepare("SHOW TABLES LIKE '$table'");
	$req->execute();
	$rowCount = $req->rowCount();
	$req->closeCursor();
	return ($rowCount > 0) ? true : false;
}
?>

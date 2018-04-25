<?php
// thp_page contains four basic object classes for formatting pages using the PureCSS library
// Page -- Sends the headers, starts the body, sends the navbar, main title and control icons
// Filters -- Sets up dropdowns that feed into the $_SESSION object
// Table -- Sets up and outputs a 2d table - also backing it up into $_SESSION["contents"];
// Form -- Sets up an editing form with validation
require("security.php"); // this version sets up up PDO object and global permission variables
// START CLASS PAGE
class Page {
    
    public $datatable = "0";
	public $time_start; // used to measure length for process
    public $links=array("print"=>"'javascript:window.print();'");
    public $hints=array("print"=>"Print this page");
    public function debug($message,$values) {
        echo("<p>$message".":"); print_r($values); echo("</p>\n");
    }
    
    public function datatable(){
        $this->datatable="1";
    }
	
	public function menu(){
		// I've not figured out how to most securely limit access to admin pages
		// so this is a bit of a hack
		$admin=$_SESSION["admin"];
		$menu=json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT']."/includes/menu.json"),true);
		echo("<div class='pure-menu pure-menu-horizontal hidden-print'>\n\t<ul class='pure-menu-list'>\n");
		foreach($menu as $key=>$links){
			if(is_array($links)) {
				if($admin or ($key<>"AF Test" and $key<>"Admin")){
					echo("\t\t<li class='pure-menu-item pure-menu-has-children pure-menu-allow-hover'>\n");
					echo("\t\t\t<a href='#' class='pure-menu-link'>$key</a>\n\t\t\t<ul class='pure-menu-children'>\n");
					foreach($links as $tag=>$link){
						echo("\t\t\t<li class='pure-menu-item'><a class='pure-menu-link' href='$link'>$tag</a></li>\n");
					}
					echo("\t\t</ul>\n\t</li>\n");
				}
			}else{
				echo("\t\t<li class='pure-menu-item'><a class='pure-menu-link' href='$links'>$key</a></li>\n");
			}
		}
		echo("\t</ul>\n</div>\n");
	}		
    
    public function start($title="THP",$lang="en"){
        foreach($_GET as $key=>$value) $_SESSION[$key]=$value;
		$this->time_start=microtime(true);        
        echo("<!DOCTYPE html>\n<html lang=$lang>\n<head>\n<title>$title</title>\n");
        echo("<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/pure/1.0.0/pure-min.css'>\n");
		echo("<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/pure/1.0.0/grids-responsive.css'>\n");
        echo("<link rel='stylesheet' href='/static/pure.thp.css'>\n");
		echo("<link rel='stylesheet' href='/static/thp.form.css'>\n");
        if($this->datatable=="1"){
?>
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/datatable/2.0.1/js/datatable.min.css"/>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/datatable/2.0.1/js/datatable.min.js"></script>
        <script>
        var datatable = new DataTable(document.querySelector('#datatable table'), {
            pageSize: 5,
            sort: [true, true, false],
            filters: [true, false, 'select'],
            filterText: 'Type to filter... ',
            pagingDivSelector: "#datatable"
        });</script>
<?php
        }
        echo("<meta charset='utf-8'>\n");
        echo("</head>\n<body>\n");
		$this->menu();
        echo("<div class=container>\n");
        echo("<h1>$title ");
        foreach($this->links as $key=>$link) {
            $hint=$this->hints[$key];
            echo("<a href=$link><img class=icon src=/static/$key.svg title='$hint'></a>\n");
        }
        echo("</h1>\n");
		$reply=$_SESSION["reply"];
		if($reply>''){
			unset($_SESSION["reply"]); 
				$color="green";
				if(substr($reply,0,5)=="Error") $color="red";
			echo("<p style='text-align:center;color:white;background-color:".$color."'>$reply</p>\n");
    	}
	}
    public function icon($type="edit",$link="/edit",$hint="Edit this record"){
        $this->links[$type]=$link;
        $this->hints[$type]=$hint;
    }
    
    public function end(){
		$time=microtime(true)-($this->time_start);
		echo("<p><i>Run time: $time</i></p>\n");
        echo("</div></body></html>\n");
    }
}
// END CLASS PAGE
// CLASS FORM - EDIT A RECORD
class Form {
    public $div1="<div class='pure-control-group'>\n<label for=";
	public $data=array();
    public function start($action=""){
        echo("<form class='pure-form pure-form-aligned' method='post'");
        if($action>'') echo (" action='$action'");
        echo(">\n<Fieldset>\n");
    }
    public function end($submit="Save Data"){
        echo("\n\n<div class='pure-controls'>".
	    '<button type="submit" class="pure-button pure-button-primary">'.$submit.'</button>'.
    	"</div>\n</fieldset>\n</form>\n");
    }
	public function data($array) { // these are the existing values
		$this->data=$array;
	}
	public function toggle($name) {
		echo("<input type=hidden name='$name' value=0>");
		$value=$this->data[$name];
		echo($this->div1."'$name'>".ucwords($name).":</label>");
        echo("<label class=switch><input type=checkbox name='$name'");
		if($value>0) echo(" CHECKED");
		echo("><span class=slider></span></label></div>\n");
	}
		
	public function num($name,$min=NULL,$max=NULL){
		$value=$this->data[$name];
		if($value=='') $value=0;
		$label=ucwords($name);
		if($min<>NULL) $label .= "$min to $max";
        echo($this->div1."'$name'>".ucwords($name).":</label>");
        echo("<input type=number name='$name' value='$value'");
        if($min<>NULL) echo(" min='$min'");
        if($max<>NULL) echo(" max='$max'");
		if($min<>NULL) echo("><span class=status></span");
        echo("></div>\n");
    }
    public function text($name,$rename='',$minlength=0){
		$label=($rename>'' ? $rename : $name);
        echo($this->div1."'$name'>".ucwords($label).":</label>");
        echo("<input type=text name='$name' value='".$this->data[$name]."'");
		if($minlength>0) echo(' required><span class=status></span');
		echo("></div>\n");
    }
    public function date($name){
        echo($this->div1."'$name'>".ucwords($name).":</label>");
        echo("<input type=date name='$name' value='".$this->data[$name]."'></div>\n");
    }
    public function textarea($name,$rename='',$required=0){
		$label=($rename>'' ? $rename : $name);
        echo($this->div1."'$name'>".ucwords($label).":</label>");
        echo("<textarea name=$name rows=5 cols=60");
		if($required) echo(" REQUIRED");
		echo(">".$this->data[$name]."</textarea>\n");
		if($required) echo("<span class=status></span>");
		echo("</div>\n");
    }
    public function hide($name,$value){
        echo("<input type=hidden name='$name' value='$value'>\n");
    }
    public function dropdown($name,$array){
        echo($this->div1."'$name'>".ucwords($name).":</label>");
        echo("<select name='$name'>\n<option value=0>(Select)\n");
        foreach($array as $key=>$value){
            echo("<option value='$key'");
            if($key==$this->data[$name]) echo(" selected");
            echo(">$value\n");
        }
        echo("</select></div>\n");
    }
	public function query($name,$db,$query){
		$this->dropdown($name,$db->query($query)->fetchAll(PDO::FETCH_KEY_PAIR));
	}
	public function record($db,$table,$id){ // goes inside start and end
		// First pull in the list of field meta data
		$pdo_stmt=$db->query("select * from $table where id='$id'");
		$this->data = $pdo_stmt->fetch(PDO::FETCH_ASSOC);
		if(!is_object($pdo_stmt)) Die("Fatal Error - bad query - $query \n");
		foreach(range(0, $pdo_stmt->columnCount() - 1) as $column_index)
		{ $meta[$column_index] = $pdo_stmt->getColumnMeta($column_index);}
		echo("<p>META: "); print_r($meta); echo("</p>\n"); // DEBUG
		echo("<p>DATA: "); print_r($data); echo("</p>\n"); // DEBUG
		$this->hide("table",$table);
		if($id>0) $this->hide("id",$id);
		foreach(range(0, $pdo_stmt->columnCount() - 1) as $column_index) {
			$name=$meta[$column_index]["name"];
			$type=$meta[$column_index]["native_type"];
			$value=$data[$column_index];
			if(!($value>'')) $value=$_SESSION["name"];
			if($type=="LONG") {
				$this->num($name);
			}elseif($type=="BLOB") {
				$this->textarea($name);
			}elseif($type=='DATE'){
				$this->date($name);		
			}else{
				$this->text($name);
			}
		}
	}
} // END OF CLASS FORM
// CLASS FILTER - dropdowns that - on change - restart the page and set $_SESSION["name"];
class Filter {
    public function start(){
        echo("<div class=pure-g>\n");
    }
    public function end(){
        echo("</div>\n");
    }
	public function range($name,$n1=1, $n2=4){
		for($i=$n1;$i<=$n2;$i++) $array[$i]=$i;
		return $this->dropdown($name,$array);
	}
	public function toggle($name,$rhs=''){
	if(!isset($_SESSION[$name])) $_SESSION[$name]="on"; // default to ON
	echo "<form class='pure-form pure-u-1 pure-u-md-1-4'>"
		."<label for='$name'>".ucfirst($name).":&nbsp;</label>"
		."<label class='switch'>&nbsp;<input name=$name type=hidden value='off'>"
		."<input type=checkbox name=$name onchange=this.form.submit();  ";
		if($_SESSION[$name]=="on") echo(" checked");
		echo("><span class='slider'></span></label>&nbsp;$rhs</form>\n");
	return $_SESSION[$name];
}
	public function query($name,$db,$query){
		$pdo_stmt=$db->query($query);
		if(is_object($pdo_stmt)){
			$array = $pdo_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
			return $this->dropdown($name,$array);
		}else{
			Die("</div>Fatal Error: bad query in dropdown: $query.");
		}
	}
	public function dropdown($name,$array){
        if (!isset($_SESSION[$name])) $_SESSION[$name] = "";
        $selected = $_SESSION[$name];
       echo "<form class='pure-form pure-u-1 pure-u-md-1-4'>" .
            "<div class='form-group'>" .
            "<label for='$name'>".ucfirst($name).":&nbsp;</label>" .
            "<select id='$name' name=$name onchange=this.form.submit(); >\n" .
			"<option value=0>(All)\n";
        foreach($array as $key=>$value) {
            echo("<option value=$key");
            if($key==$selected) echo(" SELECTED");
            echo(">$value\n");
        }
        echo("</select></div></form>\n");
		return $_SESSION[$name];
    }	
}
// END CLASS FILTER
// START CLASS TABLE
class Table { // These are public for now but may eventually be private with setters
	public $contents=array(array()); // main 2d array
	public $rowspan=0; // If>0, then start rowspan with column this many columns
	public $backmap=array();
	public $extra=array(); // extra headers
	public $ntext=1; // number of columns to not be formatted
	public $groups=array(); // headers
	public $infocol=array(); // Definitions of column headers
	public $inforow=array(); // Definitions of rows
	public $href="";
	public $dpoints=0; // Decimal points
    public function info($definition){ // return a string function with info symbol and title
	    return "<img src='/static/info.svg' height='20px' title='$definition'>";
    }
	public function rowspan($n=2){ // set number of columns to include in rowspan
		$this->rowspan=$n;
	}
	public function query($db,$query){ // load results of a query into the grid
		$pdo_stmt=$db->query($query);
		if(is_object($pdo_stmt)){
			foreach(range(0, $pdo_stmt->columnCount() - 1) as $column_index)
			{
				$meta = $pdo_stmt->getColumnMeta($column_index);
				$this->contents[0][$column_index]=$meta["name"];
			}
		}else{ 
			Die("Fatal error with query $query"); 
		}
		while($row = $pdo_stmt->fetch(PDO::FETCH_NUM)) $this->contents[]=$row;
	}
	public function join($name,$column){ // Add a column joined by first column backmap
		$nrows=sizeof($this->contents);
		$ncols=sizeof($this->ocntents[0]); // index of next open column
		$j=($sizeof($this->groups)==0 ? 0 : 1); // index of mapping column
		if(sizeof($this->backmap)==0){
			for($i=1;$i<$nrows;$i++) $this->backmap[$this->contents[$i][$j]]=$i;
		}
		$contents[0][$ncols]=$name; // Label the new column
		foreach($column as $key=>$value) $this->contents[$backmap[$key]][$ncols]=$value;
	}
			
	public function loadrows($result) { // load from the output of a pdo query
		while($row=$result->fetch(PDO::FETCH_NUM)) $this->row($row);
	}
	public function dump() {
		print_r($this->contents);
	}
	public function record($db,$table,$id){ // display one record horizontally
		$this->contents[0]=array("Field","Value");
		$result=$db->query("select * from $table where id='$id'");
		$data=$result->fetch(PDO::FETCH_ASSOC);
		foreach($data as $key=>$value) {
			$row[0]=$key; $row[1]=$value;
			$this->contents[]=$row;
		}
	}
    public function header($row) {
        $this->contents[0]=$row;
    }
    public function row($row){
        $this->contents[]=$row;
    }
	public function ntext($n=1){ // set the number of text columns
		$this->ntext=$n;
	}    
    public function groups($row) {
        $this->groups=$row;
    }
    public function inforow($array) {
        $this->inforow=$array;
    }
    public function infocol($array) {
        $this->infocol=$array;
    }
    
// SUM UP THE $contents from column $nsum onwards (counting from zero)
	public function totals(){
		$nrows=sizeof($this->contents);
		$ncols=sizeof($this->contents[0]);
		$sums=array('','');
		for($j=1;$j<$ncols;$j++){
			for($i=1;$i<$nrows;$i++) $sums[$j] += $this->contents[$i][$j];
		}
    	$sums[0]="<div ALIGN=RIGHT>TOTALS</div>";
	    $this->contents[]=$sums;
    }
	// Link any foreign keys to their dependent table name field
	public function smartquery($db,$table,$where){
		$from=" from $table a";
		$alias=97; // ascii for lowercase a
		$pdo_stmt=$db->query("select * from $table where id=1"); // we need the names of the fields
		$query="select ";
		foreach(range(0, $pdo_stmt->columnCount() - 1) as $column_index) {
			$name=$pdo_stmt->getColumnMeta($column_index)["name"];
			if(substr($name,-3)=="_ID") {
				$alias++; // go to the next lowercase letter
				$from .=" left outer join ".strtolower(substr($name,0,-3))." ".chr($alias)." on a.$name=".chr($alias).".id ";
				$query .= chr($alias).".name as ".substr($name,0,-3).", ";
			}else{
				$query .= "a.$name, ";
			}
		}
		$query=substr($query,0,-2).$from.$where." limit 1500";
		if($_SESSION["debug"]) echo("<p>Debug Smart $query</p>\n");
		$this->query($db,$query);		
	}
	// SHOW THE TABLE - Including the id column on hrefs, but do skip the groups column
	function show($href=''){
	
        // Set parameters appropriate to various options
	    $ngroups=sizeof($this->groups); // Option to group rows with subheaders
	    $ninforow=sizeof($this->inforow); // Option to show info symbols at start of row
	    $nstart=($ngroups>0 ? 1 : 0); // If groups, then don't display col 0
	    $group=-99;
		$nrows=sizeof($this->contents);
	    $ncols=sizeof($this->contents[0]);
		$nrowspan=$this->rowspan;
		// If we're doing rowspan, set up the array
		if($nrowspan) {
			$first="";
			$r=1; // keep your finger on first row in group
			for($i=1;$i<$nrows;$i++){
				if($this->contents[$i][$nstart]==$first){
					$rowspan[$r]++; $rowspan[$i]=0;
				}else{
					$r=$i; $first=$this->contents[$r][$nstart]; $rowspan[$r]=1;
				}
			}
		}
		// Debug stuff
		if($_SESSION["debug"]){
			echo("<p>Debug rowspan $nrowspan:"); print_r($rowspan); echo("</p>\n");
			echo("<p>Debug inforow $ninforow:"); print_r($this->inforow); echo("</p>\n");
			echo("<p>Debug infocol $ninfocol:"); print_r($this->infocol); echo("</p>\n");
		}
		// Start outputing the table
		echo("<table class='pure-table pure-table-striped pure-table-bordered'>\n<thead>\n");
		foreach($this->contents as $i=>$row) {
		    echo("<tr>"); // Start outputing rows
		    if($i==0){ // column headers - replace underscores with blanks to look nicer
		        for($j=$nstart;$j<$ncols;$j++) echo("<th>".str_replace("_"," ",$row[$j])."</th>");
		        echo("</tr>\n</thead>\n<tbody>\n");
		    }else{ // regular rows (perhaps preceded by a full-width bar?
				if($ngroups>0) { // output a bar based on column zero if requested
		            $g=$row[0];
		            if($g>$group) {
		                $group=$g;
		                echo("<tr><th colspan=".($ncols-1).">{$group}. ".$this->groups[$group]."</td></tr>\n");
		            }
		        }
				// Here is where all the variability comes in
				// if there are rowspans we send out the that many columns only at start of a rowspan group
				if( ($nrowspan==0) or ($rowspan[$i]>0)){ // do we output the first bits of this row or not?
					$rs=($rowspan[$i]>1 ? " rowspan=".$rowspan[$i] : ""); // is there a rowspan clause in the TDs?
					if($ninforow>0) $info=$this->info($this->inforow[$row[$nstart]]); // Does the row include an info icon?
					if($href>'') {
						echo("<td$rs><a href='".$href.$row[$nstart]."'>".$info.$row[$nstart]."</a></td>"); // a link?
					}else{ echo("<td$rs>".$info.$row[$nstart]."</td>");} // or no link
					// are there more columns within the rowspan?
					if($nrowspan>1) for($j=$nstart+1;$j<($nstart+$nrowspan);$j++) echo("<td$rs>$row[$j]</td>");
				}
				$nstart2=($rowspan>1 ? $nstart+$nrowspan : $nstart+1);
		        for($j=$nstart2;$j<$ncols;$j++) {
					$v=$row[$j];
					if ( is_numeric($v) and ($j>($this->ntext)) ) $v=number_format($v);
					echo("<td>$v</td>");
				}
                echo("</tr>\n");
		    }
		}
		echo("</tbody>\n</table>\n");
		$_SESSION["contents"]=$this->contents;
	}
}
?>

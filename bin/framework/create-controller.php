<?php
function ask(string $prompt): bool
{
    while(true) {
        $res = mb_strtolower(readline($prompt));
        if($res === "y") return true;
        if($res === "n") return false;
        
        echo "$res is not a valid answer, please try again.\n";
    }
}

chdir("app/handlers");

$name = getopt("n:")["n"];
if(file_exists($name)) exit("Controller with this name already exists!");

if(ask("Would you like to define dependencies interactively? [Y/n]")) {
    $dependencies = [];
    while(true) {
        $depndency = [
            readline("Dependency controller name: "),
            readline("Dependency controller action (__index__): "),
            readline("Dependency action parameters (JSON-encoded): "),
        ];
        $dependencies[] = $depndency;
        
        if(ask("Finish defining dependencies? [Y/n]")) break;
    }
}

if(ask("Would you like to define actions interactively? [Y/n]")) {
    $actions = [];
    while(true) {
        $actions[readline("Action name: ")] = [
            readline("Action method (press enter to skip): "),
        ];
        
        if(ask("Finish defining actions? [Y/n]")) break;
    }
}

mkdir($name);
chdir($name);
mkdir(".meta");

$xml = new SimpleXMLElement("<Controller></Controller>");

if(isset($dependencies)) {
    $deps = $xml->addChild("Dependencies", "");
    foreach($dependencies as $depndency) {
        $dep = $deps->addChild("Dependency", "");
        $dep->addAttribute("controller", $dependency[0]);
        $dep->addAttribute("action", $dependency[1]);
        $dep->addAttribute("params", $dependency[2]);
    }
}

echo "Generating controller...\n";
file_put_contents(".meta/controller.xml", $xml->asXML());
echo "Controller generation: OK\n";

if(isset($actions)) {
    echo "Generating actions... \n";
    foreach($actions as $name=>$j) {
         $filename = ($j[0] !== "")? "$name/$j[0]_handle.php" : "$name/handle.php";
         if(file_exists($name)) exit("Fatal error during action generation: this action ($name) already exists.");
         
         mkdir($name);
         file_put_contents($filename, "<?php\n\nreturn (function(\$args) {\n    echo \"Hello world!\";\n\n    return [];\n});");
    }
    echo "Actions generation: OK\n";
}

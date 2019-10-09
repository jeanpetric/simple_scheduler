<?php
    $salt = "f8dsds7gd6ft6dfd";
    
    class FormCreator {
        protected $action;
        protected $formStart;
        protected $formEnd;

        public function __construct($action) {
            $this->action = filter_var($action, FILTER_SANITIZE_STRING);;
            $this->formStart = "<form action='$this->action' method='post'>";
            $this->formEnd = "<br><br><input type='submit' /></form>";
        }

        public function addInput(FormInput $formInput) {
            $this->formStart = $this->formStart . $formInput->getInput();
        }

        public function getForm() {
            return $this->formStart . $this->formEnd;
        }
    }

    abstract class FormInput {
        protected $start;
        protected $body;
        protected $end;

        public function addBody(FormInput $element) {
            $this->body .= $element->getInput();
        }

        public function getInput() {
            return $this->start . $this->body . $this->end;
        }
    }

    class FormInputText extends FormInput {
        public function __construct($name, $placeholder) {
            $n = filter_var($name, FILTER_SANITIZE_STRING);
            $p = filter_var($placeholder, FILTER_SANITIZE_STRING);
            $this->start = "<input type='text' name='$n' placeholder='$p'>";
            $this->end = "</input>";
        }
    }

    class FormInputTextArea extends FormInput {
        public function __construct($name, $placeholder) {
            $n = filter_var($name, FILTER_SANITIZE_STRING);
            $p = filter_var($placeholder, FILTER_SANITIZE_STRING);
            $this->start = "<textarea name='$n' placeholder='$p'>";
            $this->end = "</textarea>";
        }
    }

    class FormInputHidden extends FormInput {
        public function __construct($name, $value) {
            $n = filter_var($name, FILTER_SANITIZE_STRING);
            $v = filter_var($value, FILTER_SANITIZE_STRING);
            $this->start = "<input type='hidden' name='$n' value='$v'>";
            $this->end = "</input>";
        }
    }

    class FormInputSelect extends FormInput {
        public function __construct($name) {
            $n = filter_var($name, FILTER_SANITIZE_STRING);
            $this->start = "<select name='$n'>";
            $this->end = "</select>";
        }
    }

    class FormInputOption extends FormInput {
        public function __construct($value) {
            $v = filter_var($value, FILTER_SANITIZE_STRING);
            $this->start = "<option value='$v'>" . $v;
            $this->end = "</option>";
        }
    }

    $get = $_GET;
    $post = $_POST;

    $action = $get["action"];
    $actionPost = $post["action"];

    if (isset($actionPost)) {
        $action = $actionPost;
    }

    if (!isset($action)) {
        echo "Error";
        exit(-1);
    }

    if ($action === "setup") {
        $doodle = $post["name"];
        $times = $post["times"];
        if (isset($doodle) && isset($times)) {
            $file = fopen($doodle, "w");
            $timeslots = explode("\n", $times);
            $str;
            foreach ($timeslots as $timeslot) {
                echo $timeslot . "<br>";
                echo md5($timeslot . $salt) . "<br>";
                $str .= trim($timeslot) . "\n";
            }
            fwrite($file, trim($str));
            fclose($file);
            echo "Link: doodle.php?action=doodle&name=" . $doodle;
        } else {
            $f = new FormCreator("doodle.php");
            $action = new FormInputHidden("action", "setup");
            $doodle = new FormInputText("name", "type doodle name");
            $times = new FormInputTextArea("times", "insert time slots separated by new line");
            $f->addInput($action);
            $f->addInput($doodle);
            $f->addInput($times);
            print($f->getForm());
        }
    } else if ($action === "doodle") {
        $doodle = filter_var($get["name"], FILTER_SANITIZE_STRING);
        $slots = explode("\n", file_get_contents($doodle));
        $f = new FormCreator("doodle.php");
        $doodle = new FormInputHidden("name", "$doodle");
        $action = new FormInputHidden("action", "doodle_submit");
        $name = new FormInputText("student", "your full name");
        $select = new FormInputSelect("timeslot");
        foreach ($slots as $slot) {
            $option = new FormInputOption($slot);
            $select->addBody($option);
        }
        $f->addInput($name);
        $f->addInput($select);
        $f->addInput($doodle);
        $f->addInput($action);
        print($f->getForm());
    } else if ($action === "doodle_submit") {
        $doodle = filter_var($post["name"], FILTER_SANITIZE_STRING);
        $selectedSlot = filter_var($post["timeslot"], FILTER_SANITIZE_STRING);
        $student = filter_var($post["student"], FILTER_SANITIZE_STRING);
        $slots = explode("\n", file_get_contents($doodle));
        $file = fopen($doodle, "w");
        $str;
        foreach ($slots as $slot) {
            if (trim($slot) !== trim($selectedSlot)) {
                $str .= trim($slot) . "\n";
            }
        }
        fwrite($file, trim($str));
        fclose($file);
        $record = fopen($doodle . ".selected", "a");
        fwrite($record, $student . " -> " . trim($selectedSlot) . "\n");
        fclose($record);
        echo "Thanks $student. Your selected slot $selectedSlot has been recorded.";
    } else if ($action === "show") {
        $doodle = filter_var($get['name'], FILTER_SANITIZE_STRING);
        $file = file($doodle . ".selected");
        foreach ($file as $element) {
            print($element . "<br>");
        }
    }
?>
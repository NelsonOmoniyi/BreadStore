<?php
class validation
{
    private $error      = false;
    private $messageBag = array();
    public function validate(array $request, array $rulesPair, array $fieldAlias = array())
    {
        foreach($rulesPair as $key => $val)
        {
            $rules = explode('|',$val);
            foreach($rules as $rule_name)
            {
                $fieldAlias[$key] = ($fieldAlias[$key] == '')?$key:$fieldAlias[$key];
                $this->hasMetCondition($request,$key,$rule_name,$fieldAlias[$key]);
            }
        }
        return array('error'=>$this->error,'messages'=>$this->messageBag);
    }
    public function hasMetCondition($request,$key,$rule_to_validate,$alias)
    {
        $val = $request[$key];
        if(strpos($rule_to_validate,':') == false)
        {
            if($rule_to_validate == 'required')
            {
                if($key == "*")
                {
                    foreach($request as $row=>$v)
                    {
                        $this->checkRequired($v,$alias);
                    }
                }else
                {
                    $this->checkRequired($val,$alias);
                }
                
            }
            if($rule_to_validate == 'int')
            {
                if(!is_numeric($val))
                {
                    $this->error = true;
                    $this->messageBag[] = $alias.' field must be an integer';
                }
            }
            if($rule_to_validate == 'email')
            {
                $email = filter_var($val,FILTER_SANITIZE_EMAIL);
                if(!filter_var($val,FILTER_VALIDATE_EMAIL))
                {
                    $this->error = true;
                    $this->messageBag[] = $alias.' field must be a valid email';
                }
            }
        }else
        {
            $this->numericComparism($request,$key,$rule_to_validate,$alias);
        }
            
    }
    public function numericComparism($request,$key,$rule_to_validate,$alias)
    {
        $val = $request[$key];
        $r_rule = explode(':',$rule_to_validate);
        if($r_rule[0] == 'min' && strlen($val) < $r_rule[1])
        {
            $this->error = true;
            $this->messageBag[] = $alias.' field must have a minimum of '.$r_rule[1].' character.';
            return $this->error;
        }
        if($r_rule[0] == 'max' && strlen($val) > $r_rule[1])
        {
            $this->error = true;
            $this->messageBag[] = $alias.' field must have a maximum of '.$r_rule[1].' character.';
            return $this->error;
        }
        if($r_rule[0] == 'matches')
        {
    //            echo $request[$r_rule[1]]."~".$val;
            if($val !== $request[$r_rule[1]] )
            {
                $this->error = true;
                $this->messageBag[] = $alias.' field does not match.';
                return $this->error;
            }
        }
        if($r_rule[0] == 'unique')
        {
            $tbl_field = explode('.',$r_rule[1]);
            $sql = "SELECT $tbl_field[1] FROM $tbl_field[0] WHERE $tbl_field[1] = '$val' LIMIT 1 ";
            $res = $this->db_query($sql,false);
            if($res > 0)
            {
                $this->error = true;
                $this->messageBag[] = $alias.' already exist ';
                return $this->error;
            }
        }
    }
    public function checkRequired($value,$alias)
    {
        if($value == "" || $value == null)
        {
            $this->error = true;
            $this->messageBag[] = $alias.' field is required.';
            return $this->error;
        }
    }
    
    
}
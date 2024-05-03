<?php 

namespace Model;

class Usuario extends ActiveRecord{

    protected static $tabla = 'usuarios';
    protected static $columnasDB = ['id','nombre','email','password','token','confirmado'];
    public $id;
    public $nombre;
    public $email;
    public $password;
    public $password2;
    public $token;
    public $confirmado;
    public $password_actual;
    public $password_nuevo;

    public function __construct($args = []){

        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->password2 = $args['password2'] ?? '';
        $this->password_actual = $args['password_actual'] ?? '';
        $this->password_nuevo = $args['password_nuevo'] ?? '';
        $this->token = $args['token'] ?? '';
        $this->confirmado = $args['confirmado'] ?? 0;
                                        
    }


    //Validar login
    public function validarLogin(){
        if(!$this->email){self::$alertas['error'][] = 'El email es obligatorio';}
        if(!$this->password){self::$alertas['error'][] = 'El password es obligatorio';}
        if(!filter_var($this->email,FILTER_VALIDATE_EMAIL)){self::$alertas['error'][] = 'Email no Valido';}
        
        return self::$alertas;
    }

    //Validacion para cuentas nuevas
    public function validarNuevaCuenta(){
        if(!$this->nombre){self::$alertas['error'][] = 'El nombre es obligatorio';}
        if(!$this->email){self::$alertas['error'][] = 'El email es obligatorio';}
        if(!$this->password){self::$alertas['error'][] = 'El password es obligatorio';}
        if(strlen($this->password) < 6){self::$alertas['error'][] = 'El password debe contener al menos 6 caracteres';}
        if($this->password !== $this->password2){self::$alertas['error'][] = 'El password no coincide';}
        
        return self::$alertas;
    }

    //Comprobar Password
    public function comprobar_password(){
        return password_verify($this->password_actual,$this->password);
    }


    //Hashear password
    public function hashPassword(){
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    //Generar Token
    public function crearToken(){
        $this->token = uniqid();
    }

    //Validar Email
    public function validarEmail(){
        if(!$this->email){self::$alertas['error'][] = 'El email es obligatorio';}
        if(!filter_var($this->email,FILTER_VALIDATE_EMAIL)){self::$alertas['error'][] = 'Email no Valido';}
        return self::$alertas;
    }

    //Validar password
    public function validarPassword(){
        if(!$this->password){self::$alertas['error'][] = 'El password es obligatorio';}
        if(strlen($this->password) < 6){self::$alertas['error'][] = 'El password debe contener al menos 6 caracteres';}
        return self::$alertas;
    }

    public function validar_perfil(){
        if(!$this->nombre){self::$alertas['error'][] = 'El nombre es obligatorio';}
        if(!$this->email){self::$alertas['error'][] = 'El email es obligatorio';}
        return self::$alertas;
    }

    public function nuevo_password(){
        if(!$this->password_actual){self::$alertas['error'][] = 'El password actual es obligatorio';}
        if(!$this->password_nuevo){self::$alertas['error'][] = 'El password nuevo es obligatorio';}
        if(strlen($this->password_nuevo) < 6){self::$alertas['error'][] = 'El password nuevo debe contener al menos 6 caracteres';}
        return self::$alertas;
    }
}
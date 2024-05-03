<?php
namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;

class LoginController{
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////    
    
    public static function login(Router $router){
        $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarLogin();
            if(empty($alertas)){
                //verificar que el usuario existe
                $usuario = Usuario::where('email', $usuario->email);
                if(!$usuario || !$usuario->confirmado){Usuario::setAlerta('error','El usuario no existe o no esta confirmado');}
                //El usuario existe
                else{
                    if(password_verify($_POST['password'], $usuario->password)){
                        //iniciar sesion
                        session_start();
                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;
                        //redireccionar
                        header('Location: /dashboard');
                    }
                    else{Usuario::setAlerta('error','Password incorrecto');}
                }
            }
        }
        $alertas = Usuario::getAlertas();
        //Render a la vista
        $router->render('auth/login',[
            'titulo' => 'Iniciar Sesion',
            'alertas' => $alertas
        ]);
    }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////    

    public static function logout(){
        session_start();
        $_SESSION = [];
        header('Location: /');
    }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////    
    
    public static function crear(Router $router){
        $alertas = [];
        $usuario = new Usuario;
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $usuario->sincronizar($_POST); 
            $alertas = $usuario->validarNuevaCuenta();
            if(empty($alertas)){
                $existeUsuario = Usuario::where('email',$usuario->email);
                if($existeUsuario){
                    Usuario::setAlerta('error','El usuario ya esta registrado');
                    $alertas = Usuario::getAlertas();
                }
                else{
                    //Hashear password
                    $usuario->hashPassword();
                    //Eliminar password2
                    unset($usuario->password2);
                    //Genear token
                    $usuario->crearToken();
                    //Crear Usuario
                    $resultado = $usuario->guardar();
                    //Enviar email
                    $email = new Email($usuario->email,$usuario->nombre,$usuario->token);
                    $email->enviarConfirmacion();
                    if($resultado){header('Location: /mensaje');}
                }
            }
        }
        //Render a la vista
        $router->render('auth/crear',[
            'titulo' => 'Crear Cuenta',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////    

    public static function olvide(Router $router){
        $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();

            if(empty($alertas)){
                //Buscar usuario
                $usuario = Usuario::where('email',$usuario->email);
                if($usuario && $usuario->confirmado){
                    //encontre el usuario
                    //generar nuevo token
                    $usuario->crearToken();
                    //eliminar password2
                    unset($usuario->password2);
                    //actualizar el usuario
                    $usuario->guardar();
                    //enviar email
                    $email = new Email($usuario->email,$usuario->nombre,$usuario->token);
                    $email->enviarInstrucciones();
                    //imprimir alerta
                    Usuario::setAlerta('exito','Hemos enviado las instrucciones a tu email');
                }
                else{Usuario::setAlerta('error','El usuario no existe o no esta confirmado');}
            }
        }
        $alertas = Usuario::getAlertas();
        //Render a la vista
        $router->render('auth/olvide',[
            'titulo' => 'Olvide mi Password',
            'alertas' => $alertas
        ]);
    }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////    

    public static function reestablecer(Router $router){
        $token = s($_GET['token']);
        $mostrar = true;
        if(!$token)header('Location: /');
        //Identificar el usuario con ese token
        $usuario = Usuario::where('token',$token);
        if(empty($usuario)){
            Usuario::setAlerta('error','Token no Valido');
            $mostrar = false;
        }
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            //crear nuevo password
            $usuario->sincronizar($_POST);
            //Validar password nuevo
            $alertas = $usuario->validarPassword();
            if(empty($alertas)){
                //hashear nuevo password
                $usuario->hashPassword();
                //eliminar token 
                $usuario->token = null;
                //guardar cambios en la Db
                $resultado = $usuario->guardar();
                //redireccionar
                if($resultado)header('Location: /');
            }
        }

        $alertas = Usuario::getAlertas();
        //Render a la vista
        $router->render('auth/reestablecer',[
            'titulo' => 'Reestablecer Password',
            'alertas' => $alertas,
            'mostrar' => $mostrar
        ]);
    }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////    

    public static function mensaje(Router $router){
        //Render a la vista
        $router->render('auth/mensaje',[
            'titulo' => 'Cuenta Creada Exitosamente'
        ]);
    }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////    

    public static function confirmar(Router $router){
        $token = s($_GET['token']);
        if(!$token)header('Location: /');
        //encontrar al usuario con ese token
        $usuario = Usuario::where('token',$token);
        //Token no Valido
        if(empty($usuario)){Usuario::setAlerta('error','Token no Valido');}
        //confirmar cuenta
        else{
            $usuario->confirmado = 1;
            $usuario->token = "";
            unset($usuario->password2);
            $usuario->guardar();
            Usuario::setAlerta('exito','Usuario Confirmado Correctamente');
        }

        $alertas = Usuario::getAlertas();
        //Render a la vista
        $router->render('auth/confirmar',[
            'titulo' => 'Confirmar Cuenta',
            'alertas' => $alertas
        ]);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////    
}
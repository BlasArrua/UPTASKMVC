<?php
namespace Controllers;

use Dotenv\Util\Regex;
use MVC\Router;
use Model\Tarea;
use Model\Usuario;
use Model\Proyecto;

class DashboardController {
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public static function index(Router $router){
        session_start();
        isAuth();

        $id = $_SESSION['id'];
        $proyectos = Proyecto::belongsTo('propietarioId',$id);
        
        //Render a la vista
        $router->render('dashboard/index',[
            'titulo' => 'Proyectos',
            'proyectos' => $proyectos
        ]);
    }
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public static function crear_proyecto(Router $router){
        session_start();
        isAuth();
        $alertas = [];
    
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $proyecto = new Proyecto($_POST);
            //validacion 
            $alertas = $proyecto->validarProyecto();
            if(empty($alertas)){
                //generar url unica
                $hash = md5(uniqid());
                $proyecto->url = $hash;
                //almacenar el creador del proyecto
                $proyecto->propietarioId = $_SESSION['id'];
                //guardar proyecto
                $proyecto->guardar();
                //redireccionar
                header('Location: /proyecto?id='.$proyecto->url);
            }
        }


        $router->render('dashboard/crear-proyecto',[
            'alertas' => $alertas,
            'titulo' => 'Crear Proyecto'
        ]);
    }
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public static function proyecto(Router $router){
        session_start();
        isAuth();
        $token = $_GET['id'];
        if(!$token){header('Location: /dashboard');}
        //Revisar que la persona que visita el proyecto es quien lo creo
        $proyecto = Proyecto::where('url',$token);
        if($proyecto->propietarioId !== $_SESSION['id']){header('Location: /dashboard');}
        
        $router->render('dashboard/proyecto',[
            'titulo' => $proyecto->proyecto
        ]);
    }
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public static function perfil(Router $router){
        session_start();
        isAuth();
        $alertas = [];
        $usuario = Usuario::find($_SESSION['id']);

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validar_perfil();

            if(empty($alertas)){
                $existeUsuario = Usuario::where('email',$usuario->email);
                if($existeUsuario && $existeUsuario->id !== $usuario->id){
                    //Mostrar mensaje de error
                    Usuario::setAlerta('error','Email no Valido, Ya pertenece a otra cuenta');
                    $alertas = $usuario->getAlertas();
                }
                else{
                    $usuario->guardar();
                    Usuario::setAlerta('exito','Actualizado Correctamente');
                    $alertas = $usuario->getAlertas();
                    $_SESSION['nombre'] = $usuario->nombre;
                }
            }
        }

        $router->render('dashboard/perfil',[
            'titulo' => 'Perfil',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public static function cambiar_password(Router $router){
        session_start();
        isAuth();
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $usuario = Usuario::find($_SESSION['id']);
            $usuario->sincronizar($_POST);
            $alertas = $usuario->nuevo_password();

            if(empty($alertas)){
                $resultado = $usuario->comprobar_password();
                if($resultado){
                    $usuario->password = $usuario->password_nuevo;
                    //Eliminar propiedades viejas
                    unset($usuario->password_actual);
                    unset($usuario->password_nuevo);
                    //Hashear Password nuevo
                    $usuario->hashPassword();
                    //Actualizar
                    $resultado = $usuario->guardar();
                    //Alerta
                    if($resultado){
                        Usuario::setAlerta('exito','Password Actualizado Correctamente');
                        $alertas = $usuario->getAlertas();
                    }
                }
                else{
                    Usuario::setAlerta('error','Password Incorrecto');
                    $alertas = $usuario->getAlertas();
                }
            }
        }

        

        $router->render('dashboard/cambiar-password',[
            'titulo' => 'Cambiar Password',
            'alertas' => $alertas
        ]);
    }
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public static function eliminarProyecto(){
        if($_SERVER['REQUEST_METHOD'] ==='POST'){
                
            //validar id
            $id=$_POST['id'];
            $id=filter_var($id,FILTER_VALIDATE_INT);
                    
            if($id){
                $proyecto=Proyecto::find($id);
                
                // Eliminar Tareas del Proyecto
                $tareas = Tarea::belongsTo('proyectoId', $proyecto->id);
     
                foreach ($tareas as $tarea) {
                    $tarea->eliminar();
                }
     
                $proyecto->eliminar();
                
                // Redireccionar al dashboard
                header('Location: /dashboard');
            }
        }
    }
    
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

}
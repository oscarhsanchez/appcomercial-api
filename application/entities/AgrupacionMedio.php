<?php

require_once(APPPATH.ENTITY_ESOCIAL_ENTITY);

class AgrupacionMedio extends eEntity {

    public $pk_agrupacion;
    public $fk_pais;
    public $descripcion;
    public $tipo;
    public $created_at;
    public $updated_at;
    public $token;
    public $estado;
    public $fk_ubicacion;
    public $coste;


	public function getPK() {
		return "pk_agrupacion";
	}

	//Este metodo los usamos para definir las propidades que queremos omitir durante la grabacion en bbdd
	public function unSetProperties() {
		return array ("created_at", "updated_at");
	}

	public function getTableName() {
		return "agrupacion_medios";
	}

}

?>
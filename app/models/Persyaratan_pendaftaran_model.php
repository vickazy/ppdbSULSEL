<?php
	defined('BASEPATH') OR exit('No direct script access allowed');

	class persyaratan_pendaftaran_model extends CI_Model{

		private $persyaratan_id,$tipe_sklh_id,$thn_pelajaran,$persyaratan;

		const pkey = "persyaratan_id";
		const tbl_name = "persyaratan_pendaftaran";

		function get_pkey(){
			return self::pkey;
		}		

		function get_tbl_name(){
			return self::tbl_name;
		}

		function __construct(array $init_properties=array()){

			if(count($init_properties)>0){
				foreach($init_properties as $key=>$val){
					$this->$key = $val;
				}
			}
		}

		function get_persyaratan_id() {
	        return $this->persyaratan_id;
	    }

	    function get_tipe_sklh_id() {
	        return $this->tipe_sklh_id;
	    }

	    function get_thn_pelajaran() {
	        return $this->thn_pelajaran;
	    }

	    function get_persyaratan() {
	        return $this->persyaratan;
	    }


		function set_persyaratan_id($persyaratan_id) {
	        $this->persyaratan_id = $persyaratan_id;
	    }

	    function set_tipe_sklh_id($tipe_sklh_id) {
	        $this->tipe_sklh_id = $tipe_sklh_id;
	    }

	    function set_thn_pelajaran($thn_pelajaran) {
	        $this->thn_pelajaran = $thn_pelajaran;
	    }

	    function set_persyaratan($persyaratan) {
	        $this->persyaratan = $persyaratan;
	    }

	    function get_field_list(){
			return get_object_vars($this);
		}

		function get_property_collection(){
			$field_list = get_object_vars($this);

			$collections = array();
			foreach($field_list as $key=>$val){
				if($val!='')
					$collections[$key]=$val;
			}

			return $collections;
		}

		function get_all_data(){
			$query = $this->db->query("SELECT * FROM ".$this->get_tbl_name()." ORDER BY ".$this->get_pkey()." ASC");
			return $query->result_array();
		}
	}
?>
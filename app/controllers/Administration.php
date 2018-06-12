<?php
	defined('BASEPATH') OR exit('No direct script access allowed');

	require_once APPPATH.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'Backoffice_parent.php';

	class administration extends Backoffice_parent{

		public $active_controller;

		function __construct(){
			parent::__construct();
			$this->active_controller = __CLASS__;
		}


		//SCHOOL FUNCTION PAKET
		function school(){
			$this->aah->check_access();

			$nav_id = $this->aah->get_nav_id(__CLASS__.'/school');
			$read_access = $this->aah->check_privilege('read',$nav_id);
			$add_access = $this->aah->check_privilege('add',$nav_id);

			if($read_access){
				$this->global_model->reinitialize_dao();
				$dao = $this->global_model->get_dao();
				$data['active_url'] = str_replace('::','/',__METHOD__);
				$data['dt2_rows'] = $dao->execute(0,"SELECT * FROM ref_dt2 WHERE provinsi_id='".$this->_SYS_PARAMS[1]."'")->result_array();
				$data['form_id'] = "search-school-form";
				$data['active_controller'] = $this->active_controller;
				$data['containsTable'] = true;
				$data['add_access'] = $add_access;

				$this->backoffice_template->render($this->active_controller.'/school/index',$data);
			}else{
				$this->error_403();
			}
		}

		function search_school_data(){
			$this->aah->check_access();

			$nav_id = $this->aah->get_nav_id(__CLASS__.'/school');			
			$update_access = $this->aah->check_privilege('update',$nav_id);
			$delete_access = $this->aah->check_privilege('delete',$nav_id);

			$search_dt2 = $this->input->post('search_dt2');

			$data2['update_access'] = $update_access;
			$data2['delete_access'] = $delete_access;
			$data2['rows'] = $this->get_school_data($search_dt2);
			$data2['search_dt2'] = $search_dt2;

			$data['list_of_data'] = $this->load->view($this->active_controller.'/school/list_of_data',$data2,true);

			$this->load->view($this->active_controller.'/school/data_view',$data);
		}

		function get_school_data($dt2){
			$this->global_model->reinitialize_dao();
			$dao = $this->global_model->get_dao();
			
			$cond = "";
			if(!empty($dt2))
				$cond = "WHERE a.dt2_id='".$dt2."'";

			$sql = "SELECT a.*,b.nama_dt2,c.akronim as jenjang FROM sekolah as a 
					LEFT JOIN ref_dt2 as b ON (a.dt2_id=b.dt2_id) 
					LEFT JOIN ref_tipe_sekolah as c ON (a.tipe_sekolah_id=c.ref_tipe_sklh_id)
					".$cond;
			
			$rows = $dao->execute(0,$sql)->result_array();
			return $rows;
		}

		function load_school_form(){
			$this->aah->check_access();

			$this->load->model(array('sekolah_model'));
			$this->global_model->reinitialize_dao();
			$dao = $this->global_model->get_dao();
			
			$act = $this->input->post('act');
			$search_dt2 = $this->input->post('search_dt2');

			$id_name = 'sekolah_id';

		    $m = $this->sekolah_model;
		    $id_value = ($act=='edit'?$this->input->post('id'):'');
		    $curr_data = $dao->get_data_by_id($act,$m,$id_value);

		    $dt2_opts = array();
		    $sekolah_opts = array();		    

		    $new_schoolId = $this->global_model->get_incrementID('sekolah_id','sekolah');

		    $data['sekolah_id'] = ($act=='add'?$new_schoolId:$id_value);
		    $data['jenjang_rows'] = $dao->execute(0,"SELECT * FROM ref_tipe_sekolah")->result_array();
		    $data['dt2_rows'] = $dao->execute(0,"SELECT * FROM ref_dt2 WHERE provinsi_id='".$this->_SYS_PARAMS[1]."'")->result_array();
		    $data['curr_data'] = $curr_data;
		    $data['active_controller'] = $this->active_controller;
		    $data['form_id'] = 'school-form';
		    $data['id_value'] = $id_value;
		    $data['act'] = $act;
		    $data['search_dt2'] = $search_dt2;

			$this->load->view($this->active_controller.'/school/form_content',$data);
		}

		function submit_school_data(){
			$this->aah->check_access();

			$nav_id = $this->aah->get_nav_id(__CLASS__.'/school');
			$add_access = $this->aah->check_privilege('add',$nav_id);
			$update_access = $this->aah->check_privilege('update',$nav_id);
			$delete_access = $this->aah->check_privilege('delete',$nav_id);
			$act = $this->input->post('act');

			if(($act=='add' and $add_access) or ($act=='edit' and $update_access))
			{
				$this->load->model(array('sekolah_model'));

				$this->global_model->reinitialize_dao();
				$dao = $this->global_model->get_dao();

				
				$search_dt2 = $this->input->post('search_dt2');

				$tipe_sekolah_id = $this->security->xss_clean($this->input->post('input_tipe_sekolah_id'));
				$nama_sekolah = $this->security->xss_clean($this->input->post('input_nama_sekolah'));
				$dt2_id = $this->security->xss_clean($this->input->post('input_dt2_id'));
				$alamat = $this->security->xss_clean($this->input->post('input_alamat'));
				$telepon = $this->security->xss_clean($this->input->post('input_telepon'));
				$email = $this->security->xss_clean($this->input->post('input_email'));				

				$m = $this->sekolah_model;

				$m->set_tipe_sekolah_id($tipe_sekolah_id);
				$m->set_nama_sekolah($nama_sekolah);
				$m->set_dt2_id($dt2_id);
				$m->set_alamat($alamat);
				$m->set_telepon($telepon);
				$m->set_email($email);

				if($act=='add')
				{
					$sekolah_id = $this->security->xss_clean($this->input->post('input_sekolah_id'));
					
					$m->set_sekolah_id($sekolah_id);

					$result = $dao->insert($m);
					$label = 'menyimpan';
				}
				else
				{
					$id = $this->input->post('id');
					$result = $dao->update($m,array('sekolah_id'=>$id));
					$label = 'merubah';
				}

				if(!$result)
				{
					die('ERROR: gagal '.$label.' data');
				}

				$data['update_access'] = $update_access;
				$data['delete_access'] = $delete_access;
				$data['rows'] = $this->get_school_data($search_dt2);
				$data['search_dt2'] = $search_dt2;

				$this->load->view($this->active_controller.'/school/list_of_data',$data);
			}else{
				echo 'ERROR: anda tidak diijinkan untuk '.($act=='add'?'menambah':'merubah').' data!';
			}

		}

		function delete_school_data(){
			$this->aah->check_access();

			$nav_id = $this->aah->get_nav_id(__CLASS__.'/school');
			$update_access = $this->aah->check_privilege('update',$nav_id);
			$delete_access = $this->aah->check_privilege('delete',$nav_id);

			if($delete_access)
			{
				$this->load->model(array('sekolah_model'));

				$id = $this->input->post('id');
				$search_dt2 = $this->input->post('search_dt2');

				$this->global_model->reinitialize_dao();
				$dao = $this->global_model->get_dao();

				$m = $this->sekolah_model;
				$result = $dao->delete($m,array('sekolah_id'=>$id));
				if(!$result){
					die('ERROR: gagal menghapus data');
				}
				
				$data['update_access'] = $update_access;
				$data['delete_access'] = $delete_access;
				$data['rows'] = $this->get_school_data($search_dt2);
				$data['search_dt2'] = $search_dt2;

				$this->load->view($this->active_controller.'/school/list_of_data',$data);
			}else{
				echo 'ERROR: anda tidak diijinkan untuk menghapus data!';
			}
		}

		//END OF SCHOOL FUNCTION PACKET

		//FIELD FUNCTION PAKET
		function field(){
			$this->aah->check_access();

			$nav_id = $this->aah->get_nav_id(__CLASS__.'/field');
			$read_access = $this->aah->check_privilege('read',$nav_id);
			$add_access = $this->aah->check_privilege('add',$nav_id);

			if($read_access)
			{
				$this->global_model->reinitialize_dao();
				$dao = $this->global_model->get_dao();
				$data['active_url'] = str_replace('::','/',__METHOD__);
				$data['dt2_rows'] = $dao->execute(0,"SELECT * FROM ref_dt2 WHERE provinsi_id='".$this->_SYS_PARAMS[1]."'")->result_array();
				$data['form_id'] = "search-school-form";
				$data['active_controller'] = $this->active_controller;
				$data['containsTable'] = true;
				$data['add_access'] = $add_access;
				$this->backoffice_template->render($this->active_controller.'/field/index',$data);
			}else{
				$this->error_403();
			}
		}

		function search_field_data(){
			$this->aah->check_access();

			$nav_id = $this->aah->get_nav_id(__CLASS__.'/field');			
			$update_access = $this->aah->check_privilege('update',$nav_id);
			$delete_access = $this->aah->check_privilege('delete',$nav_id);

			$search_dt2 = $this->input->post('search_dt2');

			$data2['update_access'] = $update_access;
			$data2['delete_access'] = $delete_access;
			$data2['rows'] = $this->get_field_data($search_dt2);
			$data2['search_dt2'] = $search_dt2;

			$data['list_of_data'] = $this->load->view($this->active_controller.'/field/list_of_data',$data2,true);

			$this->load->view($this->active_controller.'/field/data_view',$data);
		}

		function get_field_data($dt2){
			$this->global_model->reinitialize_dao();
			$dao = $this->global_model->get_dao();
			
			$cond = "";
			if(!empty($dt2))
				$cond = "WHERE dt2_id='".$dt2."'";

			$sql = "SELECT a.*,b.nama_sekolah FROM kompetensi_smk as a 
					INNER JOIN (SELECT sekolah_id,nama_sekolah FROM sekolah ".$cond.") as b ON (a.sekolah_id=b.sekolah_id)";
			
			$rows = $dao->execute(0,$sql)->result_array();
			return $rows;
		}

		function load_field_form(){
			$this->aah->check_access();

			$this->load->model(array('kompetensi_smk_model'));
			$this->global_model->reinitialize_dao();
			$dao = $this->global_model->get_dao();
			
			$act = $this->input->post('act');
			$search_dt2 = $this->input->post('search_dt2');

			$id_name = 'kompetensi_id';

		    $m = $this->kompetensi_smk_model;
		    $id_value = ($act=='edit'?$this->input->post('id'):'');
		    $curr_data = $dao->get_data_by_id($act,$m,$id_value);

		    $dt2_opts = array();
		    $sekolah_opts = array();		    

		    $new_fieldId = $this->global_model->get_incrementID('kompetensi_id','kompetensi_smk');

		    $sekolah_opts = array();
		    $dt2_id = '';
		    if($act=='edit'){
		    	$row = $dao->execute(0,"SELECT dt2_id FROM sekolah WHERE sekolah_id='".$curr_data['sekolah_id']."'")->row_array();
	    		$sql = "SELECT sekolah_id,nama_sekolah FROM sekolah 
	    				WHERE dt2_id='".$row['dt2_id']."'";
	    		$sekolah_opts = $dao->execute(0,$sql)->result_array();
	    		$dt2_id = $row['dt2_id'];
		    }

		    $data['kompetensi_id'] = ($act=='add'?$new_fieldId:$id_value);
		    $data['dt2_opts'] = $dao->execute(0,"SELECT * FROM ref_dt2 WHERE provinsi_id='".$this->_SYS_PARAMS[1]."'")->result_array();
		    $data['sekolah_opts'] = $sekolah_opts;
		    $data['dt2_id'] = $dt2_id;
		    $data['curr_data'] = $curr_data;
		    $data['active_controller'] = $this->active_controller;
		    $data['form_id'] = 'field-form';
		    $data['id_value'] = $id_value;
		    $data['act'] = $act;
		    $data['search_dt2'] = $search_dt2;

			$this->load->view($this->active_controller.'/field/form_content',$data);
		}

		function submit_field_data(){
			$this->aah->check_access();

			$nav_id = $this->aah->get_nav_id(__CLASS__.'/field');
			$add_access = $this->aah->check_privilege('add',$nav_id);
			$update_access = $this->aah->check_privilege('update',$nav_id);
			$delete_access = $this->aah->check_privilege('delete',$nav_id);
			$act = $this->input->post('act');

			if(($act=='add' and $add_access) or ($act=='edit' and $update_access))
			{
				$this->load->model(array('kompetensi_smk_model'));

				$this->global_model->reinitialize_dao();
				$dao = $this->global_model->get_dao();
				
				$search_dt2 = $this->input->post('search_dt2');

				$nama_kompetensi = $this->security->xss_clean($this->input->post('input_nama_kompetensi'));
				$sekolah_id = $this->security->xss_clean($this->input->post('input_sekolah_id'));

				$m = $this->kompetensi_smk_model;

				$m->set_nama_kompetensi($nama_kompetensi);
				$m->set_sekolah_id($sekolah_id);

				if($act=='add')
				{
					$kompetensi_id = $this->security->xss_clean($this->input->post('input_kompetensi_id'));
					
					$m->set_kompetensi_id($kompetensi_id);

					$result = $dao->insert($m);				
					$label = 'menyimpan';
				}
				else
				{
					$id = $this->input->post('id');
					$result = $dao->update($m,array('kompetensi_id'=>$id));
					$label = 'merubah';
				}

				if(!$result)
				{
					die('ERROR: gagal '.$label.' data');
				}

				$data['update_access'] = $update_access;
				$data['delete_access'] = $delete_access;
				$data['rows'] = $this->get_field_data($search_dt2);
				$data['search_dt2'] = $search_dt2;

				$this->load->view($this->active_controller.'/field/list_of_data',$data);

			}else{
				echo 'ERROR: anda tidak diijinkan untuk '.($act=='add'?'menambah':'merubah').' data!';
			}

		}

		function delete_field_data(){
			$this->aah->check_access();

			$nav_id = $this->aah->get_nav_id(__CLASS__.'/field');
			$update_access = $this->aah->check_privilege('update',$nav_id);
			$delete_access = $this->aah->check_privilege('delete',$nav_id);

			if($delete_access)
			{
				$this->load->model(array('kompetensi_smk_model'));

				$id = $this->input->post('id');
				$search_dt2 = $this->input->post('search_dt2');

				$this->global_model->reinitialize_dao();
				$dao = $this->global_model->get_dao();

				$m = $this->kompetensi_smk_model;
				$result = $dao->delete($m,array('kompetensi_id'=>$id));
				if(!$result){
					die('ERROR: gagal menghapus data');
				}

				$data['rows'] = $this->get_field_data($search_dt2);
				$data['search_dt2'] = $search_dt2;
				$data['update_access'] = $update_access;
				$data['delete_access'] = $delete_access;

				$this->load->view($this->active_controller.'/field/list_of_data',$data);
			}else{
				echo 'ERROR: anda tidak diijinkan untuk menghapus data!';
			}
		}

		//END OF FIELD FUNCTION PACKET



		function reset_registration(){
			$this->aah->check_access();
			
			$data['form_id'] = 'search_form';
			$data['active_url'] = str_replace('::','/',__METHOD__);
			$this->backoffice_template->render($this->active_controller.'/reset_registration/index',$data);
		}

		function search_registration_for_reset(){
			$this->aah->check_access();

			$error = 0;

			$this->load->helper(array('date_helper','mix_helper'));
			$no_pendaftaran = $this->security->xss_clean($this->input->post('src_registrasi'));

			$dao = $this->global_model->get_dao();

			$sql = "SELECT a.*,b.nama_jalur,b.tipe_sekolah_id,b.nama_tipe_sekolah,b.akronim
					FROM pendaftaran as a 
					LEFT JOIN (SELECT x.id_pendaftaran,x.tipe_sekolah_id,y.nama_jalur,z.nama_tipe_sekolah,z.akronim
									FROM pendaftaran_jalur_pilihan as x LEFT JOIN ref_jalur_pendaftaran as y 
									ON (x.jalur_id=y.ref_jalur_id)
									LEFT JOIN ref_tipe_sekolah as z ON (x.tipe_sekolah_id=z.ref_tipe_sklh_id)
									) as b 
					ON (a.id_pendaftaran=b.id_pendaftaran)
					WHERE a.no_pendaftaran='".$no_pendaftaran."';";
						
			$pendaftaran_row = $dao->execute(0,$sql)->row_array();
			$sekolah_pilihan_rows = array();
			if(count($pendaftaran_row)>0){

				$sql = "SELECT COUNT(1) as n FROM pendaftaran_sekolah_pilihan as a WHERE a.id_pendaftaran='".$pendaftaran_row['id_pendaftaran']."' AND a.status<>'0'";
				
				$row = $dao->execute(0,$sql)->row_array();
				
				if($row['n']==0){
					if($pendaftaran_row['tipe_sekolah_id']=='1')
					{
						$sql = "SELECT b.nama_sekolah FROM pendaftaran_sekolah_pilihan as a LEFT JOIN sekolah as b ON (a.sekolah_id=b.sekolah_id)";
					}else{
						$sql = "SELECT b.nama_kompetensi,c.nama_sekolah FROM pendaftaran_kompetensi_pilihan as a LEFT JOIN kompetensi_smk as b ON (a.kompetensi_id=b.kompetensi_id) 
								LEFT JOIN sekolah as c ON (a.sekolah_id=c.sekolah_id)";
					}
					$sql .= " WHERE a.id_pendaftaran='".$pendaftaran_row['id_pendaftaran']."'";
					$sekolah_pilihan_rows = $dao->execute(0,$sql)->result_array();
				}else{
					$error = 2;
				}

			}else{
				$error = 1;
			}

			$data['error'] = $error;
			$data['pendaftaran_row'] = $pendaftaran_row;
			$data['sekolah_pilihan_rows'] = $sekolah_pilihan_rows;

			$this->load->view($this->active_controller.'/reset_registration/form',$data);
		}



		function submit_reset_registration(){
			$this->aah->check_access();

			$this->load->model(array('pendaftaran_model','pendaftaran_jalur_pilihan_model','pendaftaran_sekolah_pilihan_model',
									 'pendaftaran_kompetensi_pilihan_model','pendaftaran_nilai_un_model','pendaftaran_prestasi_model',
									 'pendaftaran_dokumen_kelengkapan_model'));

			$id_pendaftaran = $this->input->post('reset_id_pendaftaran');
			$no_pendaftaran = $this->input->post('reset_no_pendaftaran');
			$jalur_id = $this->input->post('reset_jalur_id');
			$nama_jalur = $this->input->post('reset_nama_jalur');			
			$nama = $this->input->post('reset_nama');
			$jk = $this->input->post('reset_jk');
			$alamat = $this->input->post('reset_alamat');
			$sekolah_asal = $this->input->post('reset_sekolah_asal');			
			$tipe_sekolah = $this->input->post('reset_tipe_sekolah');

			$this->global_model->reinitialize_dao();
			$dao = $this->global_model->get_dao();

			$m1 = $this->pendaftaran_model;
			$arr_m = array();			
			$arr_m[] = $this->pendaftaran_jalur_pilihan_model;
			$arr_m[] = $this->pendaftaran_sekolah_pilihan_model;
			$arr_m[] = $this->pendaftaran_kompetensi_pilihan_model;
			$arr_m[] = $this->pendaftaran_nilai_un_model;
			$arr_m[] = $this->pendaftaran_prestasi_model;
			$arr_m[] = $this->pendaftaran_dokumen_kelengkapan_model;

			$this->db->trans_begin();
			
			$params = array('id_pendaftaran'=>$id_pendaftaran);

			$m1->set_status('0');
			$m1->set_no_pendaftaran('0');

			$result = $dao->update($m1,$params);
			
			foreach($arr_m as $m){
				
				$result = $dao->delete($m,$params);
				if(!$result){
					$this->db->trans_rollback();
					die('ERROR: gagal mereset pendaftaran');
				}

			}

			$this->db->trans_commit();
			//end of transaction
			
			$data['id_pendaftaran'] = $id_pendaftaran;
			$data['no_pendaftaran'] = $no_pendaftaran;
			$data['nama'] = $nama;
			$data['jk'] = $jk;
			$data['sekolah_asal'] = $sekolah_asal;			
			$data['tipe_sekolah'] = $tipe_sekolah;
			$data['nama_jalur'] = $nama_jalur;
			$data['alamat'] = $alamat;

			$this->load->view($this->active_controller.'/reset_registration/reset_result',$data);

		}




		function verification(){
			$this->aah->check_access();
			
			$nav_id = $this->aah->get_nav_id(__CLASS__.'/verification');
			$read_access = $this->aah->check_privilege('read',$nav_id);

			if($read_access)
			{
				$kompetensi_rows = array();

				if($this->session->userdata('tipe_sekolah')=='2'){
					$dao = $this->global_model->get_dao();
					$sql = "SELECT * FROM kompetensi_smk WHERE sekolah_id='".$this->session->userdata('sekolah_id')."'";
					$kompetensi_rows = $dao->execute(0,$sql)->result_array();
				}

				$data['form_id'] = 'search_form';
				$data['api_key'] = $this->_SYS_PARAMS[3];
				$data['kompetensi_rows'] = $kompetensi_rows;
				$data['active_url'] = str_replace('::','/',__METHOD__);
				$data['active_controller'] = $this->active_controller;

				$this->backoffice_template->render($this->active_controller.'/verification/index',$data);
			}else{
				$this->error_403();
			}
		}

		function settlement(){
			$this->aah->check_access();

			$nav_id = $this->aah->get_nav_id(__CLASS__.'/verification');
			$read_access = $this->aah->check_privilege('read',$nav_id);

			if($read_access)
			{
				$kompetensi_rows = array();

				if($this->session->userdata('tipe_sekolah')=='2'){
					$dao = $this->global_model->get_dao();
					$sql = "SELECT * FROM kompetensi_smk WHERE sekolah_id='".$this->session->userdata('sekolah_id')."'";
					$kompetensi_rows = $dao->execute(0,$sql)->result_array();
				}

				$data['form_id'] = 'search_form';
				$data['kompetensi_rows'] = $kompetensi_rows;
				$data['active_url'] = str_replace('::','/',__METHOD__);
				$data['active_controller'] = $this->active_controller;

				$this->backoffice_template->render($this->active_controller.'/settlement/index',$data);
			}else{
				$this->error_403();
			}
		}		
		
		function submit_settlement(){
			$this->aah->check_access();

			$this->load->model(array('pendaftaran_model','pendaftaran_sekolah_pilihan_model',
									 'pendaftaran_kompetensi_pilihan_model','log_status_pendaftaran_model'));
			$this->load->helper('date_helper');

			$id_pendaftaran = $this->input->post('verifikasi_id_pendaftaran');
			$no_pendaftaran = $this->input->post('verifikasi_no_pendaftaran');
			$jalur_id = $this->input->post('verifikasi_jalur_id');
			$nama_jalur = $this->input->post('verifikasi_nama_jalur');
			$sekolah_id = $this->input->post('verifikasi_sekolah_id');
			$nama_sekolah = $this->input->post('verifikasi_nama_sekolah');
			$kompetensi_id = $this->input->post('verifikasi_kompetensi_id');
			$nama = $this->input->post('verifikasi_nama');
			$jk = $this->input->post('verifikasi_jk');
			$alamat = $this->input->post('verifikasi_alamat');
			$nama_kecamatan = $this->input->post('verifikasi_nama_kecamatan');
			$nama_dt2 = $this->input->post('verifikasi_nama_dt2');
			$sekolah_asal = $this->input->post('verifikasi_sekolah_asal');
			$tipe_jalur = $this->input->post('verifikasi_tipe_jalur');
			$tipe_sekolah_id = $this->input->post('verifikasi_tipe_sekolah_id');
			$nama_tipe_sekolah = $this->input->post('verifikasi_nama_tipe_sekolah');

			$this->global_model->reinitialize_dao();
			$dao = $this->global_model->get_dao();

			$m1 = $this->pendaftaran_model;
			$m2 = $this->pendaftaran_sekolah_pilihan_model;
			$m3 = $this->pendaftaran_kompetensi_pilihan_model;

			$this->db->trans_begin();

			$m1->set_status('2');
			$result = $dao->update($m1,array('id_pendaftaran'=>$id_pendaftaran));
			if(!$result){
				$this->db->trans_rollback();
				die('ERROR: gagal mendaftar ulang');
			}


			$m2->set_status('5');
			$result = $dao->update($m2,array('id_pendaftaran'=>$id_pendaftaran,'sekolah_id'=>$sekolah_id));
			if(!$result){
				$this->db->trans_rollback();
				die('ERROR: gagal mendaftar ulang');
			}

			if($tipe_sekolah_id=='2'){
				$m3->set_status('5');
				$result = $dao->update($m3,array('id_pendaftaran'=>$id_pendaftaran,'kompetensi_id'=>$kompetensi_id));
				if(!$result){
					$this->db->trans_rollback();
					die('ERROR: gagal mendaftar ulang');
				}
			}

			//insert registration log
			$m = $this->log_status_pendaftaran_model;

			$wkt_daftar_ulang = date('Y-m-d H:i:s');

			$m->set_id_pendaftaran($id_pendaftaran);
			$m->set_thn_pelajaran($this->_SYS_PARAMS[0]);
			$m->set_status('3');
			$m->set_jalur_id($jalur_id);
			$m->set_sekolah_id($sekolah_id);
			$m->set_created_time($wkt_daftar_ulang);
			$m->set_user($this->session->userdata('username'));
			$result = $dao->insert($m);
			if(!$result){
				$this->db->trans_rollback();
				die('ERROR: gagal menyimpan verifikasi');
			}

			$this->db->trans_commit();
			//end of transaction
			
			$encoded_regid = base64_encode($id_pendaftaran);
			$encoded_fieldid = base64_encode($kompetensi_id);

			$data['id_pendaftaran'] = $id_pendaftaran;
			$data['no_pendaftaran'] = $no_pendaftaran;
			$data['nama'] = $nama;
			$data['jk'] = $jk;
			$data['sekolah_asal'] = $sekolah_asal;
			$data['sekolah_id'] = $sekolah_id;
			$data['nama_sekolah'] = $nama_sekolah;
			$data['kompetensi_id'] = $kompetensi_id;
			$data['tipe_sekolah_id'] = $tipe_sekolah_id;
			$data['nama_tipe_sekolah'] = $nama_tipe_sekolah;
			$data['jalur_id'] = $jalur_id;
			$data['nama_jalur'] = $nama_jalur;
			$data['alamat'] = $alamat;
			$data['nama_kecamatan'] = $nama_kecamatan;
			$data['nama_dt2'] = $nama_dt2;
			$data['encoded_regid'] = $encoded_regid;
			$data['encoded_fieldid'] = $encoded_fieldid;
			$data['active_controller'] = $this->active_controller;

			$x_wkt_daftar_ulang = explode(' ',$wkt_daftar_ulang);
			$tgl_daftar_ulang = $x_wkt_daftar_ulang[0];
			$jam_daftar_ulang = $x_wkt_daftar_ulang[1];
			
			$data['tgl_daftar_ulang'] = $tgl_daftar_ulang;
			$data['jam_daftar_ulang'] = $jam_daftar_ulang;

			$this->load->view($this->active_controller.'/settlement/settlement_result',$data);

		}

		function submit_verification(){
			$this->aah->check_access();
			$this->load->helper('date_helper');
			$this->load->model(array('pendaftaran_model','pendaftaran_dokumen_kelengkapan_model','pendaftaran_sekolah_pilihan_model',
									 'pendaftaran_kompetensi_pilihan_model','pendaftaran_nilai_un_model','pendaftaran_prestasi_model',
									 'log_status_pendaftaran_model'));

			$id_pendaftaran = $this->input->post('verifikasi_id_pendaftaran');
			$no_pendaftaran = $this->input->post('verifikasi_no_pendaftaran');
			$jalur_id = $this->input->post('verifikasi_jalur_id');
			$nama_jalur = $this->input->post('verifikasi_nama_jalur');
			$sekolah_id = $this->input->post('verifikasi_sekolah_id');
			$nama_sekolah = $this->input->post('verifikasi_nama_sekolah');
			$sekolah_pilihan_ke = $this->input->post('verifikasi_sekolah_pilihan_ke');
			$kompetensi_id = $this->input->post('verifikasi_kompetensi_id');
			$nama_kompetensi = $this->input->post('verifikasi_nama_kompetensi');
			$nama = $this->input->post('verifikasi_nama');
			$jk = $this->input->post('verifikasi_jk');
			$alamat = $this->input->post('verifikasi_alamat');
			$nama_kecamatan = $this->input->post('verifikasi_nama_kecamatan');
			$nama_dt2 = $this->input->post('verifikasi_nama_dt2');
			$sekolah_asal = $this->input->post('verifikasi_sekolah_asal');
			$tipe_jalur = $this->input->post('verifikasi_tipe_jalur');
			$tipe_sekolah_id = $this->input->post('verifikasi_tipe_sekolah_id');
			$nama_tipe_sekolah = $this->input->post('verifikasi_nama_tipe_sekolah');

			$nil_bhs_indonesia = (!is_null($this->input->post('verifikasi_nil_bhs_indonesia'))?'1':'0');
			$nil_bhs_inggris = (!is_null($this->input->post('verifikasi_nil_bhs_inggris'))?'1':'0');
			$nil_matematika = (!is_null($this->input->post('verifikasi_nil_matematika'))?'1':'0');
			$nil_ipa = (!is_null($this->input->post('verifikasi_nil_ipa'))?'1':'0');
			$tot_nilai = (!is_null($this->input->post('verifikasi_tot_nilai'))?'1':'0');
			$n_berkas = $this->input->post('verifikasi_n_berkas');
			
			$x_jarak = explode(' ',$this->security->xss_clean($this->input->post('verifikasi_jarak')));
			$jarak =  str_replace(",","",$x_jarak[0]);

			$this->global_model->reinitialize_dao();
			$dao = $this->global_model->get_dao();

			$this->db->trans_begin();

			$status1 = ($tot_nilai==='1');

			$m = $this->pendaftaran_nilai_un_model;
			$m->set_status_nil_bhs_indonesia($nil_bhs_indonesia);			
			$m->set_status_nil_bhs_inggris($nil_bhs_inggris);
			$m->set_status_nil_matematika($nil_matematika);
			$m->set_status_nil_ipa($nil_ipa);

			$result = $dao->update($m,array('id_pendaftaran'=>$id_pendaftaran));
			if(!$result){
				$this->db->trans_rollback();
				die('ERROR: gagal menyimpan verifikasi');
			}


			//insert pendaftaran_dokumen_kelengkapan
			$m = $this->pendaftaran_dokumen_kelengkapan_model;			
			$status2 = true;
			for($i=1;$i<=$n_berkas;$i++){

				$berkas_id = $this->input->post('verifikasi_berkas_id'.$i);
				$status = !is_null($this->input->post('verifikasi_berkas'.$i));
				$_status = (!is_null($this->input->post('verifikasi_berkas'.$i))?'1':'2');
				$m->set_status($_status);
				$result = $dao->update($m,array('dokel_id'=>$berkas_id));
				if(!$result){
					$this->db->trans_rollback();
					die('ERROR: gagal menyimpan verifikasi');
				}

				if($status2)
				{
					$status2 = $status2 && $status;
				}
			}

			$status3 = true;
			if($jalur_id=='4'){
				$m = $this->pendaftaran_prestasi_model;
				$n_prestasi = $this->input->post('verifikasi_n_prestasi');
				$status3 = false;

				for($i=1;$i<=$n_prestasi;$i++){

					$prestasi_id = $this->input->post('verifikasi_prestasi_id'.$i);

					$status = !is_null($this->input->post('verifikasi_prestasi'.$i));
					$_status = (!is_null($this->input->post('verifikasi_prestasi'.$i))?'1':'2');
					$m->set_status($_status);
					$result = $dao->update($m,array('prestasi_id'=>$prestasi_id));
					if(!$result){
						$this->db->trans_rollback();
						die('ERROR: gagal menyimpan verifikasi');
					}

					if($status && !$status3)
					{
						$status3 = true;
					}
				}
			}


			$status_pendaftaran = (!$status1 || !$status2 || !$status3?'2':'1');

			$m = $this->pendaftaran_sekolah_pilihan_model;
			if($tipe_sekolah_id=='1' and $tipe_jalur=='1')
				$m->set_jarak_sekolah($jarak);						

			$m->set_status('1');
			$m->set_status($status_pendaftaran);
			$result = $dao->update($m,array('id_pendaftaran'=>$id_pendaftaran,'sekolah_id'=>$sekolah_id));
			if(!$result){
				$this->db->trans_rollback();
				die('ERROR: gagal menyimpan verifikasi');
			}


			if($tipe_sekolah_id=='2'){
				$m = $this->pendaftaran_kompetensi_pilihan_model;
				$m->set_status('1');
				$m->set_status($status_pendaftaran);
				$result = $dao->update($m,array('id_pendaftaran'=>$id_pendaftaran,'kompetensi_id'=>$kompetensi_id));
				if(!$result){
					$this->db->trans_rollback();
					die('ERROR: gagal menyimpan verifikasi');
				}
			}

			//insert registration log
			$m = $this->log_status_pendaftaran_model;

			$wkt_verifikasi = date('Y-m-d H:i:s');

			$m->set_id_pendaftaran($id_pendaftaran);
			$m->set_thn_pelajaran($this->_SYS_PARAMS[0]);
			$m->set_status('1');
			$m->set_jalur_id($jalur_id);
			$m->set_sekolah_id($sekolah_id);
			$m->set_created_time($wkt_verifikasi);
			$m->set_user($this->session->userdata('username'));
			$result = $dao->insert($m);
			if(!$result){
				$this->db->trans_rollback();
				die('ERROR: gagal menyimpan verifikasi');
			}


			$this->db->trans_commit();
			//end of transaction

			$data['id_pendaftaran'] = $id_pendaftaran;
			$data['no_pendaftaran'] = $no_pendaftaran;
			$data['nama'] = $nama;
			$data['jk'] = $jk;
			$data['sekolah_asal'] = $sekolah_asal;
			$data['sekolah_id'] = $sekolah_id;
			$data['nama_sekolah'] = $nama_sekolah;
			$data['sekolah_pilihan_ke'] = $sekolah_pilihan_ke;
			$data['kompetensi_id'] = $kompetensi_id;
			$data['nama_kompetensi'] = $nama_kompetensi;
			$data['tipe_sekolah_id'] = $tipe_sekolah_id;
			$data['nama_tipe_sekolah'] = $nama_tipe_sekolah;
			$data['jalur_id'] = $jalur_id;
			$data['nama_jalur'] = $nama_jalur;
			$data['alamat'] = $alamat;
			$data['nama_kecamatan'] = $nama_kecamatan;
			$data['nama_dt2'] = $nama_dt2;
			$data['status_pendaftaran'] = $status_pendaftaran;

			$x_wkt_verifikasi = explode(' ',$wkt_verifikasi);
			$tgl_verifikasi = $x_wkt_verifikasi[0];
			$jam_verifikasi = $x_wkt_verifikasi[1];
			
			$data['tgl_verifikasi'] = $tgl_verifikasi;
			$data['jam_verifikasi'] = $jam_verifikasi;

			$data['active_controller'] = $this->active_controller;

			$this->load->view($this->active_controller.'/verification/verification_result',$data);
			
		}

		function ranking_process(){			

			$this->load->helper(array('mix_helper','date_helper'));
			$this->load->model(array('pendaftaran_model','pendaftaran_sekolah_pilihan_model','pendaftaran_kompetensi_pilihan_model',
									 'log_status_pendaftaran_model','hasil_seleksi_model'));
			$this->load->library('PPDB_ranking','','rank');

			$this->global_model->reinitialize_dao();
			$dao = $this->global_model->get_dao();

			$id_pendaftaran = $this->input->post('ranking_id_pendaftaran');
			$sekolah_id = $this->input->post('ranking_sekolah_id');
			$kompetensi_id = $this->input->post('ranking_kompetensi_id');
			$nama_kompetensi = $this->input->post('ranking_nama_kompetensi');
			$tipe_sekolah_id = $this->input->post('ranking_tipe_sekolah_id');
			$nama_tipe_sekolah = $this->input->post('ranking_nama_tipe_sekolah');
			$jalur_id = $this->input->post('ranking_jalur_id');
			$nama_jalur = $this->input->post('ranking_nama_jalur');
			$no_pendaftaran = $this->input->post('ranking_no_pendaftaran');
			$nama = $this->input->post('ranking_nama');
			$jk = $this->input->post('ranking_jk');
			$sekolah_asal = $this->input->post('ranking_sekolah_asal');
			$alamat = $this->input->post('ranking_alamat');
			$nama_kecamatan = $this->input->post('ranking_nama_kecamatan');
			$nama_dt2 = $this->input->post('ranking_nama_dt2');
			$nama_sekolah = $this->input->post('ranking_nama_sekolah');
			$sekolah_pilihan_ke = $this->input->post('ranking_sekolah_pilihan_ke');
			$tgl_verifikasi = $this->input->post('ranking_tgl_verifikasi');

			if($tipe_sekolah_id=='1')
			{
				$sql = "SELECT kuota_domisili,kuota_afirmasi,kuota_akademik,kuota_prestasi,kuota_khusus FROM pengaturan_kuota_sma
						WHERE sekolah_id='".$sekolah_id."' AND thn_pelajaran='".$this->_SYS_PARAMS[0]."'";
			}else{
				$sql = "SELECT kuota_domisili,kuota_afirmasi,kuota_akademik,kuota_prestasi,kuota_khusus FROM pengaturan_kuota_smk
						WHERE sekolah_id='".$sekolah_id."' AND kompetensi_id='".$kompetensi_id."' AND thn_pelajaran='".$this->_SYS_PARAMS[0]."'";
			}
			$kuota_row = $dao->execute(0,$sql)->row_array();

			switch($jalur_id){
				case '1':$kuota = $kuota_row['kuota_domisili'];break;
				case '2':$kuota = $kuota_row['kuota_afirmasi'];break;
				case '3':$kuota = $kuota_row['kuota_akademik'];break;
				case '4':$kuota = $kuota_row['kuota_prestasi'];break;
				case '5':$kuota = $kuota_row['kuota_khusus'];break;
				default:$kuota=0;
			}

			$this->db->trans_begin();

			$m1 = $this->hasil_seleksi_model;
			$m2 = $this->pendaftaran_sekolah_pilihan_model;
			$m3 = $this->pendaftaran_kompetensi_pilihan_model;

			$this->rank->set_dbAccess_needs($id_pendaftaran,$sekolah_id,$tipe_sekolah_id,$kompetensi_id,$jalur_id,$this->_SYS_PARAMS[0],$dao);
			
			$opponents = $this->rank->set_opponents(1);			
			
			$myReg = $this->rank->set_myReg(1);
			
			if(!$myReg or !$opponents){
				
				die('ERROR: gagal menetapkan peringkat');
			}
			
			if($jalur_id=='4'){

				$sql = "SELECT tkt_kejuaraan_id, peringkat FROM pendaftaran_prestasi WHERE id_pendaftaran='".$id_pendaftaran."'
						AND status='1' ORDER BY tkt_kejuaraan_id,peringkat ASC LIMIT 0,1";
				$prestasi_row = $dao->execute(0,$sql)->row_array();
				
				$this->rank->set_levelAchievement($prestasi_row['tkt_kejuaraan_id']);
				$this->rank->set_rateAchievement($prestasi_row['peringkat']);
			}

			$this->rank->process();

			$m1->set_jalur_id($jalur_id);
			$m1->set_sekolah_id($sekolah_id);
			$m1->set_tipe_sekolah_id($tipe_sekolah_id);
			$m1->set_kompetensi_id($kompetensi_id);
			$m1->set_thn_pelajaran($this->_SYS_PARAMS[0]);

			$result = $dao->delete($m1,array('sekolah_id'=>$sekolah_id,'kompetensi_id'=>$kompetensi_id,'jalur_id'=>$jalur_id));
			if(!$result){
				$this->db->trans_rollback();
				die('ERROR: gagal menetapkan peringkat');
			}			

			$condM2 = array('sekolah_id'=>$sekolah_id);
			if($tipe_sekolah_id=='2')
				$condM3 = array('kompetensi_id'=>$kompetensi_id);

			$idPendaftaran_arr = array();

			foreach($this->rank->get_rankList() as $row){

				if(!in_array($row['id_pendaftaran'],$idPendaftaran_arr))
				{
					$idPendaftaran_arr[] = $row['id_pendaftaran'];

					$hasil_id = $this->global_model->get_incrementID('hasil_id','hasil_seleksi');
					$m1->set_hasil_id($hasil_id);
					$m1->set_id_pendaftaran($row['id_pendaftaran']);
					$m1->set_pilihan_ke($row['pilihan_ke']);
					$m1->set_score($row['score']);
					$m1->set_peringkat($row['peringkat']);
					$result = $dao->insert($m1);
					if(!$result){
						$this->db->trans_rollback();
						die('ERROR: gagal menetapkan peringkat');
					}

					$condM2['id_pendaftaran'] = $row['id_pendaftaran'];
					$m2->set_status(($row['peringkat']<=$kuota?'3':'4'));
					$result = $dao->update($m2,$condM2);
					if(!$result){
						$this->db->trans_rollback();
						die('ERROR: gagal menetapkan peringkat');
					}

					if($tipe_sekolah_id=='2')
					{
						$condM3['id_pendaftaran'] = $row['id_pendaftaran'];
						$m3->set_status(($row['peringkat']<=$kuota?'3':'4'));
						$result = $dao->update($m3,$condM3);
						if(!$result){
							$this->db->trans_rollback();
							die('ERROR: gagal menetapkan peringkat');
						}
					}
				}
			}

			//insert registration log
			$m = $this->log_status_pendaftaran_model;

			$wkt_ranking = date('Y-m-d H:i:s');

			$m->set_id_pendaftaran($id_pendaftaran);
			$m->set_thn_pelajaran($this->_SYS_PARAMS[0]);
			$m->set_status('2');
			$m->set_jalur_id($jalur_id);
			$m->set_sekolah_id($sekolah_id);
			$m->set_created_time($wkt_ranking);
			$m->set_user($this->session->userdata('username'));
			$result = $dao->insert($m);
			if(!$result){
				$this->db->trans_rollback();
				die('ERROR: gagal menyimpan verifikasi');
			}


			$this->db->trans_commit();

			$myRank = $this->rank->get_myRank();

			$encoded_regid = base64_encode($id_pendaftaran);
			$encoded_fieldid = base64_encode($kompetensi_id);
			
			$data['id_pendaftaran'] = $id_pendaftaran;
			$data['no_pendaftaran'] = $no_pendaftaran;
			$data['nama'] = $nama;
			$data['jk'] = $jk;
			$data['sekolah_asal'] = $sekolah_asal;			
			$data['alamat'] = $alamat;
			$data['nama_kecamatan'] = $nama_kecamatan;
			$data['nama_dt2'] = $nama_dt2;
			$data['nama_sekolah'] = $nama_sekolah;
			$data['kompetensi_id'] = $kompetensi_id;
			$data['nama_kompetensi'] = $nama_kompetensi;
			$data['sekolah_pilihan_ke'] = $sekolah_pilihan_ke;
			$data['tipe_sekolah_id'] = $tipe_sekolah_id;
			$data['nama_tipe_sekolah'] = $nama_tipe_sekolah;
			$data['tgl_verifikasi'] = $tgl_verifikasi;
			$data['jalur'] = $nama_jalur;
			$data['peringkat'] = $myRank[0];
			$data['score'] = $myRank[1];
			$data['active_controller'] = $this->active_controller;
			$data['encoded_regid'] = $encoded_regid;
			$data['encoded_fieldid'] = $encoded_fieldid;

			$this->load->view($this->active_controller.'/verification/ranking_result',$data);

		}

		function search_verification(){
			
			$this->aah->check_access();

			$error = 0;

			$this->load->helper(array('date_helper','mix_helper'));
			$no_pendaftaran = $this->security->xss_clean($this->input->post('src_registrasi'));
			$jenis_kompetensi = $this->security->xss_clean($this->input->post('src_kompetensi'));

			$dao = $this->global_model->get_dao();

			$sql = "SELECT a.*,b.nama_kecamatan,c.nama_dt2,d.nama_jalur
					FROM pendaftaran as a 
					LEFT JOIN ref_kecamatan as b ON (a.kecamatan_id=b.kecamatan_id) 
					LEFT JOIN ref_dt2 as c ON (a.dt2_id=c.dt2_id)
					LEFT JOIN (SELECT x.id_pendaftaran,x.jalur_id,y.nama_jalur,z.ktg_jalur_id
						FROM pendaftaran_jalur_pilihan as x 
						LEFT JOIN ref_jalur_pendaftaran as y ON (x.jalur_id=y.ref_jalur_id)
						LEFT JOIN (SELECT jalur_id,ktg_jalur_id FROM pengaturan_kuota_jalur WHERE thn_pelajaran='".$this->_SYS_PARAMS[0]."' 
							AND tipe_sekolah_id='".$this->session->userdata('tipe_sekolah')."') as z ON (x.jalur_id=z.jalur_id)) as d 
					ON (a.id_pendaftaran=d.id_pendaftaran)
					LEFT JOIN pendaftaran_nilai_un as e ON (a.id_pendaftaran=e.id_pendaftaran)
					WHERE a.no_pendaftaran='".$no_pendaftaran."'";
						
			$pendaftaran_row = $dao->execute(0,$sql)->row_array();
			$sekolah_pilihan_row = array();

			if(count($pendaftaran_row)>0)
			{
				if($this->session->userdata('tipe_sekolah')=='1')
				{
					$sql = "SELECT a.id_pendaftaran,'0' as kompetensi_id,'' as nama_kompetensi,b.nama_sekolah,
							b.tipe_sekolah_id,b.nama_tipe_sekolah,b.akronim,
							a.sekolah_id,a.pilihan_ke,a.status
							FROM pendaftaran_sekolah_pilihan as a 
							LEFT JOIN (SELECT x.sekolah_id,x.nama_sekolah,x.tipe_sekolah_id,y.nama_tipe_sekolah,y.akronim FROM sekolah as x 
										LEFT JOIN ref_tipe_sekolah as y ON (x.tipe_sekolah_id=y.ref_tipe_sklh_id)) as b ON (a.sekolah_id=b.sekolah_id) 
							WHERE a.id_pendaftaran='".$pendaftaran_row['id_pendaftaran']."' AND 
							a.sekolah_id='".$this->session->userdata('sekolah_id')."'";
				}else{
					$sql = "SELECT a.id_pendaftaran,a.kompetensi_id,a.sekolah_id,b.nama_sekolah,
							b.nama_tipe_sekolah,b.akronim,b.tipe_sekolah_id,
							c.nama_kompetensi,a.pilihan_ke,a.status
							FROM pendaftaran_kompetensi_pilihan as a 
							LEFT JOIN (SELECT x.sekolah_id,x.nama_sekolah,x.tipe_sekolah_id,y.nama_tipe_sekolah FROM sekolah as x 
									  LEFT JOIN ref_tipe_sekolah as y ON (x.tipe_sekolah_id=y.ref_tipe_sklh_id)) as b ON (a.sekolah_id=b.sekolah_id)  
							LEFT JOIN kompetensi_smk as c ON (a.kompetensi_id=c.kompetensi_id)
							WHERE a.id_pendaftaran='".$pendaftaran_row['id_pendaftaran']."' AND 
							a.kompetensi_id='".$jenis_kompetensi."'";
				}

				$sql .= " AND a.status='3'";
				
				$sekolah_pilihan_row = $dao->execute(0,$sql)->row_array();

				if(count($sekolah_pilihan_row)>0){
					if($sekolah_pilihan_row['status']=='5'){
						$error = 2;
					}
				}else{
					$error = 1;
				}
			}else{
				$error = 1;
			}

			$data['pendaftaran_row']=$pendaftaran_row;
			$data['sekolah_pilihan_row']=$sekolah_pilihan_row;
			$data['form_id'] = 'settlement-form';
			$data['active_controller'] = $this->active_controller;
			$data['error'] = $error;
			$this->load->view($this->active_controller.'/settlement/form',$data);

		}

		function search_registration()
		{
			$this->aah->check_access();

			$error = 0;
			$this->load->helper(array('date_helper','mix_helper'));
			$no_pendaftaran = $this->security->xss_clean($this->input->post('src_registrasi'));
			$jenis_kompetensi = $this->security->xss_clean($this->input->post('src_kompetensi'));

			$dao = $this->global_model->get_dao();

			$sql = "SELECT a.*,b.nama_kecamatan,c.nama_dt2,
					d.nama_jalur,d.jalur_id,d.ktg_jalur_id as tipe_jalur,
					e.nil_bhs_indonesia,e.nil_bhs_inggris,e.nil_matematika,e.nil_ipa,e.tot_nilai
					FROM pendaftaran as a 
					LEFT JOIN ref_kecamatan as b ON (a.kecamatan_id=b.kecamatan_id) 
					LEFT JOIN ref_dt2 as c ON (a.dt2_id=c.dt2_id)
					LEFT JOIN (SELECT w.id_pendaftaran,w.jalur_id,y.nama_jalur,z.ktg_jalur_id
						FROM pendaftaran_jalur_pilihan as w						
						LEFT JOIN ref_jalur_pendaftaran as y ON (w.jalur_id=y.ref_jalur_id)
						LEFT JOIN (SELECT jalur_id,ktg_jalur_id FROM pengaturan_kuota_jalur WHERE thn_pelajaran='".$this->_SYS_PARAMS[0]."' 
							AND tipe_sekolah_id='".$this->session->userdata('tipe_sekolah')."') as z ON (w.jalur_id=z.jalur_id)) as d 
					ON (a.id_pendaftaran=d.id_pendaftaran)
					LEFT JOIN pendaftaran_nilai_un as e ON (a.id_pendaftaran=e.id_pendaftaran)
					WHERE a.no_pendaftaran='".$no_pendaftaran."'";
						
			$pendaftaran_row = $dao->execute(0,$sql)->row_array();
			$sekolah_pilihan_row = array();
			$dokumen_rows = array();
			$prestasi_rows = array();
			$status_urutan = true;

			if(count($pendaftaran_row)>0)
			{
				$sql = "SELECT a.*,b.nama_dokumen FROM pendaftaran_dokumen_kelengkapan as a LEFT JOIN ref_dokumen_persyaratan as b ON (a.dokumen=b.ref_dokumen_id) 
						WHERE a.id_pendaftaran='".$pendaftaran_row['id_pendaftaran']."'";
				$dokumen_rows = $dao->execute(0,$sql)->result_array();

				if($this->session->userdata('tipe_sekolah')=='1')
				{
					$sql = "SELECT a.id_pendaftaran,'0' as kompetensi_id,b.nama_sekolah,b.latitude,b.longitude,b.tipe_sekolah_id,b.nama_tipe_sekolah,b.akronim,
							b.alamat as alamat_sekolah,a.sekolah_id,a.pilihan_ke,a.status
							FROM pendaftaran_sekolah_pilihan as a 
							LEFT JOIN (SELECT x.sekolah_id,x.nama_sekolah,x.latitude,x.longitude,x.alamat,x.tipe_sekolah_id,y.nama_tipe_sekolah,y.akronim 
								FROM sekolah as x LEFT JOIN ref_tipe_sekolah as y ON (x.tipe_sekolah_id=y.ref_tipe_sklh_id)) as b ON (a.sekolah_id=b.sekolah_id) 
							WHERE a.id_pendaftaran='".$pendaftaran_row['id_pendaftaran']."' AND 
							a.sekolah_id='".$this->session->userdata('sekolah_id')."'";
				}else{
					$sql = "SELECT a.id_pendaftaran,a.kompetensi_id,b.nama_sekolah,b.latitude,b.longitude,
							b.alamat as alamat_sekolah,a.sekolah_id,b.tipe_sekolah_id,b.nama_tipe_sekolah,b.akronim,
							c.nama_kompetensi,a.pilihan_ke,a.status
							FROM pendaftaran_kompetensi_pilihan as a 
							LEFT JOIN (SELECT x.sekolah_id,x.nama_sekolah,x.latitude,x.longitude,x.alamat,x.tipe_sekolah_id,y.nama_tipe_sekolah,y.akronim 
										FROM sekolah as x LEFT JOIN ref_tipe_sekolah as y ON (x.tipe_sekolah_id=y.ref_tipe_sklh_id)) as b ON (a.sekolah_id=b.sekolah_id)  
							LEFT JOIN kompetensi_smk as c ON (a.kompetensi_id=c.kompetensi_id)
							WHERE a.id_pendaftaran='".$pendaftaran_row['id_pendaftaran']."' AND 
							a.kompetensi_id='".$jenis_kompetensi."'";
				}
				

				$sekolah_pilihan_row = $dao->execute(0,$sql)->row_array();				
				
				if(count($sekolah_pilihan_row)>0)
				{
					if($sekolah_pilihan_row['status']=='0')
					{
						if($sekolah_pilihan_row['pilihan_ke']>1){

							$prev_pilihan = $sekolah_pilihan_row['pilihan_ke']-1;
							$sql = "SELECT status FROM ".($this->session->userdata('tipe_sekolah')=='1'?'pendaftaran_sekolah_pilihan':'pendaftaran_kompetensi_pilihan')." 
									WHERE id_pendaftaran='".$sekolah_pilihan_row['id_pendaftaran']."' 
									AND pilihan_ke='".$prev_pilihan."' LIMIT 0,1";

							$row = $dao->execute(0,$sql)->row_array();

							if($row['status']!='0'){
								if($pendaftaran_row['jalur_id']=='4'){
									$sql = "SELECT a.*,b.tingkat_kejuaraan,c.bidang_kejuaraan FROM pendaftaran_prestasi as a 
											LEFT JOIN ref_tingkat_kejuaraan as b ON (a.tkt_kejuaraan_id=b.ref_tkt_kejuaraan_id)
											LEFT JOIN ref_bidang_kejuaraan as c ON (a.bdg_kejuaraan_id=c.ref_bdg_kejuaraan_id) 
											WHERE a.id_pendaftaran='".$pendaftaran_row['id_pendaftaran']."'";
									$prestasi_rows = $dao->execute(0,$sql)->result_array();
								}
							}else{
								$error = 3; //prev choise haven't been verified
							}
						}
						
					}else{
						$error = 2; //verified;
					}
				}else{
					$error = 1; //not found
				}

			}
			else{
				$error = 1; //not found;
			}

			$data['pendaftaran_row']=$pendaftaran_row;
			$data['sekolah_pilihan_row']=$sekolah_pilihan_row;
			$data['dokumen_rows']=$dokumen_rows;
			$data['prestasi_rows']=$prestasi_rows;
			$data['status_urutan']=$status_urutan;
			$data['provinsi'] = $this->_SYS_PARAMS[2];			
			$data['form_id'] = 'verification-form';
			$data['error'] = $error;
			$data['active_controller'] = $this->active_controller;
			$this->load->view($this->active_controller.'/verification/form_wizard',$data);
		}

		function get_verification_data($type){
			$dao = $this->global_model->get_dao();
			
			$cond = " WHERE a.sekolah_id='".$this->session->userdata('sekolah_id')."'";
			if($type==1)
				$cond .= " AND (status='1' or status='2' or status='3' or status='4')";
			else
				$cond .= " AND status='5'";
			
			if($this->session->userdata('tipe_sekolah')=='1')
			{
				$sql = "SELECT a.id_pendaftaran,'' as kompetensi_id,b.nama,b.alamat,b.sekolah_asal,
						b.no_pendaftaran,b.jk,b.nama_dt2,c.jalur_id
						FROM pendaftaran_sekolah_pilihan as a 
						LEFT JOIN (SELECT x.nama,x.id_pendaftaran,x.alamat,x.sekolah_asal,x.no_pendaftaran,x.jk,y.nama_dt2 FROM pendaftaran as x 
							LEFT JOIN ref_dt2 as y ON (x.dt2_id=y.dt2_id)) as b ON (a.id_pendaftaran=b.id_pendaftaran)
						LEFT JOIN pendaftaran_jalur_pilihan as c ON (a.id_pendaftaran=c.id_pendaftaran)";
			}else{
				$sql = "SELECT a.id_pendaftaran,a.kompetensi_id,b.nama,b.alamat,b.sekolah_asal,
						b.no_pendaftaran,b.jk,b.nama_dt2,c.nama_kompetensi,d.jalur_id
						FROM pendaftaran_kompetensi_pilihan as a 
						LEFT JOIN (SELECT x.nama,x.id_pendaftaran,x.alamat,x.sekolah_asal,x.no_pendaftaran,x.jk,y.nama_dt2 FROM pendaftaran as x 
							LEFT JOIN ref_dt2 as y ON (x.dt2_id=y.dt2_id)) as b ON (a.id_pendaftaran=b.id_pendaftaran)
						LEFT JOIN kompetensi_smk as c ON (a.kompetensi_id=c.kompetensi_id) 
						LEFT JOIN pendaftaran_jalur_pilihan as c ON (a.id_pendaftaran=d.id_pendaftaran)";
			}

			$sql .= $cond;			

			$result = $dao->execute(0,$sql);
			$rows = array();
			if($result)
				$rows = $result->result_array();
			return $rows;
		}

		function load_verification_list(){

			$nav_id = $this->aah->get_nav_id(__CLASS__.'/verification');
			$delete_access = $this->aah->check_privilege('delete',$nav_id);

			$data['tipe_sekolah'] = $this->session->userdata('tipe_sekolah');
			$data['rows'] = $this->get_verification_data(1);
			$data['delete_access'] = $delete_access;
			$data['active_controller'] = $this->active_controller;
			$this->load->view($this->active_controller.'/verification/list_of_data',$data);
		}

		function delete_verification(){
			$this->aah->check_access();

			$this->load->model(array('pendaftaran_kompetensi_pilihan_model','pendaftaran_sekolah_pilihan_model',
									 'log_status_pendaftaran_model','hasil_seleksi_model'));
			$id_pendaftaran = $this->input->post('id_pendaftaran');
			$kompetensi_id = $this->input->post('kompetensi_id');
			$jalur_id = $this->input->post('jalur_id');

			$dao = $this->global_model->get_dao();

			$this->db->trans_begin();			

			if($this->session->userdata('tipe_sekolah')=='1')
			{	
				$m2 = $this->pendaftaran_sekolah_pilihan_model;			
				$cond1 = array('id_pendaftaran'=>$id_pendaftaran);
				$cond2 = array('id_pendaftaran'=>$id_pendaftaran,'sekolah_id'=>$this->session->userdata('sekolah_id'));
			}else{
				$m2 = $this->pendaftaran_kompetensi_pilihan_model;
				$cond1 = array('id_pendaftaran'=>$id_pendaftaran,'kompetensi_id'=>$kompetensi_id);
				$cond2 = $cond1;
			}

			$m1 = $this->hasil_seleksi_model;			
			$result = $dao->delete($m1,$cond1);
			if(!$result){
				$this->db->trans_rollback();
				die('failed');
			}

			$m2->set_status('0');
			$result = $dao->update($m2,$cond2);
			if(!$result){
				$this->db->trans_rollback();
				die('failed');
			}

			//insert registration log
			$m = $this->log_status_pendaftaran_model;

			$wkt_hapus = date('Y-m-d H:i:s');

			$m->set_id_pendaftaran($id_pendaftaran);
			$m->set_thn_pelajaran($this->_SYS_PARAMS[0]);
			$m->set_status('4');
			$m->set_jalur_id($jalur_id);
			$m->set_sekolah_id($this->session->userdata('sekolah_id'));
			$m->set_created_time($wkt_hapus);
			$m->set_user($this->session->userdata('username'));
			$result = $dao->insert($m);
			if(!$result){
				$this->db->trans_rollback();
				die('failed');
			}



			$this->db->trans_commit();

			$this->load_verification_list();
		}		

		function load_settlement_list(){
			$nav_id = $this->aah->get_nav_id(__CLASS__.'/settlement');
			$delete_access = $this->aah->check_privilege('delete',$nav_id);

			$data['tipe_sekolah'] = $this->session->userdata('tipe_sekolah');
			$data['rows'] = $this->get_verification_data(2);
			$data['delete_access'] = $delete_access;
			$data['active_controller'] = $this->active_controller;

			$this->load->view($this->active_controller.'/settlement/list_of_data',$data);
		}

		function delete_settlement(){
			$this->aah->check_access();
			
			$this->load->model(array('pendaftaran_kompetensi_pilihan_model','pendaftaran_sekolah_pilihan_model','log_status_pendaftaran_model'));
			$id_pendaftaran = $this->input->post('id_pendaftaran');
			$kompetensi_id = $this->input->post('kompetensi_id');
			$jalur_id = $this->input->post('jalur_id');

			$dao = $this->global_model->get_dao();

			$this->db->trans_begin();

			if($this->session->userdata('tipe_sekolah')=='1')
			{	
				$m = $this->pendaftaran_sekolah_pilihan_model;							
				$cond = array('id_pendaftaran'=>$id_pendaftaran,'sekolah_id'=>$this->session->userdata('sekolah_id'));
			}else{
				$m = $this->pendaftaran_kompetensi_pilihan_model;
				$cond = array('id_pendaftaran'=>$id_pendaftaran,'kompetensi_id'=>$kompetensi_id);				
			}			

			$m->set_status('3');
			$result = $dao->update($m,$cond);
			if(!$result){
				$this->db->trans_rollback();
				die('failed');
			}

			//insert registration log
			$m = $this->log_status_pendaftaran_model;

			$wkt_hapus = date('Y-m-d H:i:s');

			$m->set_id_pendaftaran($id_pendaftaran);
			$m->set_thn_pelajaran($this->_SYS_PARAMS[0]);
			$m->set_status('5');
			$m->set_jalur_id($jalur_id);
			$m->set_sekolah_id($this->session->userdata('sekolah_id'));
			$m->set_created_time($wkt_hapus);
			$m->set_user($this->session->userdata('username'));
			$result = $dao->insert($m);
			if(!$result){
				$this->db->trans_rollback();
				die('failed');
			}


			$this->db->trans_commit();

			$this->load_settlement_list();
		}		

		function get_administration_result($id_pendaftaran,$type,$kompetensi_id=''){
			$sql = "SELECT a.*,b.nama_kecamatan,c.nama_dt2,d.nama_jalur
					FROM pendaftaran as a 
					LEFT JOIN ref_kecamatan as b ON (a.kecamatan_id=b.kecamatan_id) 
					LEFT JOIN ref_dt2 as c ON (a.dt2_id=c.dt2_id)
					LEFT JOIN (SELECT w.id_pendaftaran,w.jalur_id,y.nama_jalur
						FROM pendaftaran_jalur_pilihan as w	LEFT JOIN ref_jalur_pendaftaran as y ON (w.jalur_id=y.ref_jalur_id)) as d 
					ON (a.id_pendaftaran=d.id_pendaftaran)
					WHERE a.id_pendaftaran='".$id_pendaftaran."'";
			
			$this->global_model->reinitialize_dao();
			$dao = $this->global_model->get_dao();

			$pendaftaran_row = $dao->execute(0,$sql)->row_array();

			if($this->session->userdata('tipe_sekolah')=='1')
			{
				$sql = "SELECT a.id_pendaftaran,'0' as kompetensi_id,b.nama_sekolah,b.tipe_sekolah_id,
						b.nama_tipe_sekolah,b.akronim,a.pilihan_ke,c.score
						FROM pendaftaran_sekolah_pilihan as a 
						LEFT JOIN (SELECT x.sekolah_id,x.nama_sekolah,x.tipe_sekolah_id,y.nama_tipe_sekolah,y.akronim 
							FROM sekolah as x LEFT JOIN ref_tipe_sekolah as y ON (x.tipe_sekolah_id=y.ref_tipe_sklh_id)) as b ON (a.sekolah_id=b.sekolah_id) 
						LEFT JOIN hasil_seleksi as c ON (a.id_pendaftaran=c.id_pendaftaran AND a.sekolah_id=c.sekolah_id)
						WHERE a.id_pendaftaran='".$id_pendaftaran."' AND 
						a.sekolah_id='".$this->session->userdata('sekolah_id')."'";
			}else{
				$sql = "SELECT a.id_pendaftaran,a.kompetensi_id,b.nama_sekolah,b.tipe_sekolah_id,
						b.nama_tipe_sekolah,b.akronim,
						c.nama_kompetensi,a.pilihan_ke,d.score
						FROM pendaftaran_kompetensi_pilihan as a 
						LEFT JOIN (SELECT x.sekolah_id,x.nama_sekolah,x.tipe_sekolah_id,y.nama_tipe_sekolah,y.akronim 
									FROM sekolah as x LEFT JOIN ref_tipe_sekolah as y ON (x.tipe_sekolah_id=y.ref_tipe_sklh_id)) as b ON (a.sekolah_id=b.sekolah_id)  
						LEFT JOIN kompetensi_smk as c ON (a.kompetensi_id=c.kompetensi_id)
						LEFT JOIN hasil_seleksi as d ON (a.id_pendaftaran=d.id_pendaftaran AND a.sekolah_id=d.sekolah_id)
						WHERE a.id_pendaftaran='".$id_pendaftaran."' AND a.kompetensi_id='".$kompetensi_id."'";
			}
			
			$sekolah_pilihan_row = $dao->execute(0,$sql)->row_array();

			$sql = "SELECT DATE_FORMAT(created_time,'%Y-%m-%d') as tgl_verifikasi FROM log_status_pendaftaran WHERE id_pendaftaran='".$id_pendaftaran."' AND status='".$type."' 
					ORDER BY log_status_id DESC";
			$log_status_row = $dao->execute(0,$sql)->row_array();
			
			return array('pendaftaran_row'=>$pendaftaran_row,'sekolah_pilihan_row'=>$sekolah_pilihan_row,'log_status_row'=>$log_status_row);
		}

		function print_verification($encoded_regid,$encoded_fieldid=''){
			$this->aah->check_access();

			$this->load->helper('date_helper');
			
			$decoded_regid = base64_decode(urldecode($encoded_regid));
			$decoded_fieldid = base64_decode(urldecode($encoded_fieldid));

			$data = $this->get_administration_result($decoded_regid,'1',$decoded_fieldid);

			$this->load->view($this->active_controller.'/verification/print_verification',$data);
			
		}

		function print_settlement($encoded_regid,$encoded_fieldid=''){

			$this->aah->check_access();

			$this->load->helper('date_helper');
			
			$decoded_regid = base64_decode(urldecode($encoded_regid));
			$decoded_fieldid = base64_decode(urldecode($encoded_fieldid));

			$data = $this->get_administration_result($decoded_regid,'3',$decoded_fieldid);

			$this->load->view($this->active_controller.'/settlement/print_settlement',$data);
		}
	}
?>
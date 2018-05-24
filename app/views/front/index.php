
    <!-- SERVICES -->
    <div id="conditions" class="conditions">
        <div class="container">
            <h2>Ketentuan Umum</h2>
            <?php
                
                echo "<ol type='1' align='left'>";
                foreach($ketentuan_umum_rows as $row){
                    echo "<li>".$row['ketentuan']."</li>";
                }
                echo "</ol>";

            ?>

        </div>
    </div>
    <!-- END SERVICES -->

    <!-- REQUIREMENTS -->
    <div id="requirements" class="requirements">
        <div class="container">
            <div class="row">
                <h2>Persyaratan Calon Peserta Didik Baru</h2>

                <?php                    
                    $rows1 = $tipe_sekolah_rows;
                    $cw = 12/count($rows1);
                    $alp = "A";

                    $params = [$_SYS_PARAMS[0]];

                    foreach($tipe_sekolah_rows as $row1){

                        $params[1] = $row1['ref_tipe_sklh_id'];
                        $persyaratan_pendaftaran_dao->set_sql_params($params);
                        $query2 = $persyaratan_pendaftaran_dao->Execute();
                        $rows2 = $query2->result_array();

                        echo "
                        <div class='col-lg-".$cw." col-md-".$cw." wow flipInY' data-wow-delay='.8s'>
                            <div class='packages' style='height:300px'>
                                <h4>".$alp.". ".$row1['nama_tipe_sekolah']." (".$row1['akronim'].")</h4><br />
                                <ol type='1' align='left'>";
                                    foreach($rows2 as $row2){
                                        echo "<li>".$row2['persyaratan']."</li>";
                                    }
                                echo "</ol>
                            </div>
                        </div>";

                        $alp++;
                    }
                ?>
                
            </div>
        </div>
    </div>
    <!-- END REQUIREMENTS -->

    <!-- STAGE -->
    <div id="stage" class="stage">
        <div class="container">
            <h2>Jenjang Pendidikan Pilihan</h2>
            <diiv class="row">
            <?php
                $rows1 = $tipe_sekolah_rows;
                $params = [$_SYS_PARAMS[0]];

                $img_path = $this->config->item('img_path');                

                foreach($rows1 as $row1){

                    $params[1] = $row1['ref_tipe_sklh_id'];

                    echo "
                    <div class='col-lg-6 col-md-6 wow flipInY' data-wow-delay='.8s'>
                        <div class='packages'>
                            <div class='row'>
                                <img src='".$img_path."icon_anak_".strtolower($row1['akronim']).".png' alt='Anak ".$row1['akronim']."'/>
                                <h4>".$row1['nama_tipe_sekolah']." (".$row1['akronim'].")</h4><br />
                                <center><a href='".base_url()."reg/stage/".$row1['ref_tipe_sklh_id']."' class='btn btn-success'><i class='fa fa-check'></i> Daftar</a></center>
                            </div>
                            <div class='path-list'>
                                <h4>Jalur Pendaftaran :</h4>
                                <ul>";
                                    foreach($jalur_pendaftaran_rows as $row2){

                                        if($row2['status_jadwal']=='0' || $row2['status_jadwal']==null || $row2['status_jadwal']==''){
                                            $jadwal = "<font color='orange'>Belum buka</font>";
                                        }else if($row2['status_jadwal']=='1'){                                            
                                            $jadwal = "<font color='green'>Buka</font> ".$row2['tgl_buka'].", <font color='red'>Tutup</font> ".$row2['tgl_tutup']."</font>";
                                        }else{
                                            $jadwal = "<font color='red'>Tutup</font>";
                                        }

                                        echo "<li>".$row2['nama_jalur']." | <b>".$jadwal."</b> <span class='badge badge-primary pull-right'>".$row2['nama_ktg_jalur']."</span></li>";

                                    }
                                echo "</ul>
                            </div>
                        </div>
                    </div>";
                }
            ?>
            </div>
                
        </div>
    </div>
    <!-- END PATH -->
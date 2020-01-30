<?php

    //
    $arrayBuku = [
        [
            'name' => 'Pemograman PHP 7',
            'rating' => [
                'user1' => '5',
                'user2' => '1',
                'user3' => '2',
                'user4' => '4'
            ]
        ],
        [
            'name' => 'Arah Langkah',
            'rating' => [
                'user1' => '1',
                'user2' => '5',
                'user4' => '3'
            ]
        ],
        [
            'name' => 'Rentang Kisah',
            'rating' => [
                'user2' => '2',
                'user3' => '3',
                'user4' => '5'
            ]
        ],
        [
            'name' => 'Logika Pemograman Python',
            'rating' => [
                'user1' => '2',
                'user2' => '5',
                'user3' => '5'
            ]
        ],
        [
            'name' => 'Sprint',
            'rating' => [
                'user1' => '2',
                'user2' => '5',
                'user3' => '4'
            ]
        ],
    ];

    //ambil data dari database
    include("koneksi.php");

    $sql = "SELECT * FROM data_buku WHERE rating != '0'";
    $query = mysqli_query($koneksi, $sql);
    
    $arrayBuku = Array();
    while($data = mysqli_fetch_array($query)){
        
        $sql2 = "SELECT * FROM user a, ulasan_user b WHERE a.id = b.user_id AND b.buku_no = '$data[No]'";
        $query2 = mysqli_query($koneksi, $sql2);

        $arrayDatabase = null;
        while($data2 = mysqli_fetch_array($query2)){
            $arrayDatabase[$data2['name']] = $data2['rating'];
        }

        $databaseArray = [
            'name' => $data['jdl_buku'],
            'rating' => $arrayDatabase
        ];

        array_push($arrayBuku, $databaseArray);
        unset($arrayDatabase);
    }
    
    //end ambil data dari database

    //Pearson Correlation

    //run person corelation
    function collaborativeFiltering($arrayBuku){
        $arrayAverageAll = average($arrayBuku);
        $arrayInteractionAll = interaction($arrayAverageAll);
        $arraySimilarity = similarity($arrayInteractionAll);
        $arrayNeighborAll = neighbor($arrayBuku, $arraySimilarity);
        $arrayPredictionAll = prediction($arrayNeighborAll);

        return $arrayPredictionAll;
    }
    //end run person corelation

//=================================SECTION 1==================================================

    //hitung rata rata rating
    function average($arrayBuku){
        $arrayAverageAll = Array();

        foreach($arrayBuku as $result){
            
            $countAverage = 0;
            $sumRating = 0;
            foreach($result['rating'] as $key => $value){
                $countAverage++;
                $sumRating = $sumRating + $value;
            }

            $arrayAverage['name'] = $result['name'];
            $arrayAverage['rating'] = $result['rating'];
            $arrayAverage['average'] = $sumRating/$countAverage;
            array_push($arrayAverageAll, $arrayAverage);
        }

        return $arrayAverageAll;
    }
    //end hitung rata rata rating

//=================================SECTION 2==================================================
    
    //cek interaction
    function interaction($arrayAverageAll){
        $arrayAverageAll2 = $arrayAverageAll;
        $arrayInteractionAll = Array();
        $cek['countOld'] = 0;
        
        foreach($arrayAverageAll as $result){

            $cek['countNew'] = 0;
            
            foreach($arrayAverageAll2 as $result2){
                
                if($cek['countNew'] > $cek['countOld']){
                    
                    $rating['ratingAll1'] = Array();
                    $rating['ratingAll2'] = Array();
                    $rating1 = null;
                    $rating2 = null;
                    $rating3 = null;
                    $rating4 = null;
                    
                    $rateOld = 0;
                    foreach($result['rating'] as $key => $value){
                        
                        $rateNew = 0;
                        foreach($result2['rating'] as $key2 => $value2){
                            if($key == $key2){
                                $rating1[$key] = $value;
                                $rating2[$key.'Two'] = $value2;
                            }

                            if($rateOld == 0){
                                if (!array_key_exists($key, $result2['rating'])){
                                    $rating3[$key] = $value;
                                }

                                if (!array_key_exists($key2, $result['rating'])){
                                    $rating4[$key2] = $value2;
                                }
                            }else{
                                if (!array_key_exists($key, $result2['rating'])){
                                    $rating3[$key] = $value;
                                }
                                if (!array_key_exists($key2, $result['rating']) && $rateNew > $rateOld){
                                    $rating4[$key2] = $value2;
                                }   
                            }

                            $rateNew++;
                        }

                        $rateOld++;
                    }
                    
                    if($rating1 != null && $rating2 != null){
                        $interaction['interaction'] = [
                            'name' => $result['name'],
                            'nameTwo' => $result2['name'],
                            'rating' => $rating1,
                            'ratingTwo' => $rating2,
                            'ratingElse' => $rating3,
                            'ratingElseTwo' => $rating4,
                            'average' => $result['average'],
                            'averageTwo' => $result2['average']
                        ];
                        array_push($arrayInteractionAll, $interaction['interaction']);
                    }
                }
                $cek['countNew']++;
            }
            $cek['countOld']++;
        }

        return $arrayInteractionAll;
    }
    //end cek interaction

//=================================SECTION 3==================================================

    //hitung similaritas

    //rumus similarity
    function similarity($arrayInteractionAll){
        $rumusHead = rumusHead($arrayInteractionAll);
        $rumusFoot = rumusFoot($rumusHead);
        $arraySimilarity = Array();

        foreach($rumusFoot as $result){

            if($result['rumusFoot'] != 0){
                $similarityArray = [
                    'name' => $result['name'],
                    'nameTwo' => $result['nameTwo'],
                    'rating' => $result['rating'],
                    'ratingTwo' => $result['ratingTwo'],
                    'ratingElse' => $result['ratingElse'],
                    'ratingElseTwo' => $result['ratingElseTwo'],
                    'average' => $result['average'],
                    'averageTwo' => $result['averageTwo'],
                    'rumusHead' => $result['rumusHead'],
                    'rumusFoot' => $result['rumusFoot'],
                    'similarity' => $result['rumusHead']/$result['rumusFoot']
    
                ];
                array_push($arraySimilarity, $similarityArray);
            }
        }

        return $arraySimilarity;
    }
    //end rumus similarity

    //rumus pecah head
    function rumusHead($arrayInteractionAll){

        $arrayRumusHead = Array();

        foreach($arrayInteractionAll as $result){

            $rumusHead = 0;
            foreach($result['rating'] as $key => $value){
                
                foreach($result['ratingTwo'] as $result2){
                    if(array_key_exists($key.'Two', $result['ratingTwo'])){
                        $rumusHead = $rumusHead + (($value-$result['average'])*($result['ratingTwo'][$key.'Two']-$result['averageTwo']));
                        break;
                    }
                }
                
            }

            $rumusHeadArray = [
                'name' => $result['name'],
                'nameTwo' => $result['nameTwo'],
                'rating' => $result['rating'],
                'ratingTwo' => $result['ratingTwo'],
                'ratingElse' => $result['ratingElse'],
                'ratingElseTwo' => $result['ratingElseTwo'],
                'average' => $result['average'],
                'averageTwo' => $result['averageTwo'],
                'rumusHead' => $rumusHead,
            ];
            array_push($arrayRumusHead, $rumusHeadArray);

        }

        return $arrayRumusHead;
    }
    //end rumus pecah head

    //rumus pecah foot
    function rumusFoot($rumusHead){

        $arrayRumusFoot = Array();

        foreach($rumusHead as $result){

            $rumusFootA = 0;
            foreach($result['rating'] as $key => $value){
                $rumusFootA = $rumusFootA + (($value-$result['average'])*($value-$result['average']));
            }

            $rumusFootB = 0;
            foreach($result['ratingTwo'] as $key2 => $value2){
                $rumusFootB = $rumusFootB + (($value2-$result['averageTwo'])*($value2-$result['averageTwo']));
            }

            $rumusFoot = sqrt($rumusFootA*$rumusFootB);

            $rumusFoodArray = [
                'name' => $result['name'],
                'nameTwo' => $result['nameTwo'],
                'rating' => $result['rating'],
                'ratingTwo' => $result['ratingTwo'],
                'ratingElse' => $result['ratingElse'],
                'ratingElseTwo' => $result['ratingElseTwo'],
                'average' => $result['average'],
                'averageTwo' => $result['averageTwo'],
                'rumusHead' => $result['rumusHead'],
                'rumusFoot' => $rumusFoot
            ];
            array_push($arrayRumusFoot, $rumusFoodArray);

        }

        return $arrayRumusFoot;
    }
    //end rumus pecah foot

    //end hitung similaritas

//=================================SECTION 4==================================================

    //menentukan neighbor
    function neighbor($arrayBuku, $arraySimilarity){
        $arrayBuku2 = $arrayBuku;
        $arrayNeighborAll = Array();

        foreach($arrayBuku as $result){
            
            $neighborArray = Array();
            foreach($arraySimilarity as $result2){

                    if($result['name'] == $result2['name']){
                        if($result2['similarity'] > 0){
                            if($result2['ratingElseTwo'] != null || $result2['ratingElseTwo'] != ''){
                                $neighborData = [
                                    'name' => $result2['nameTwo'],
                                    'similarity' => $result2['similarity'],
                                    'ratingElse' => $result2['ratingElseTwo']
                                ];

                                array_push($neighborArray, $neighborData);
                            }
                        }
                    }else if($result['name'] == $result2['nameTwo']){
                        if($result2['similarity'] > 0){
                            if($result2['ratingElse'] != null || $result2['ratingElse'] != ''){
                                $neighborData = [
                                    'name' => $result2['name'],
                                    'similarity' => $result2['similarity'],
                                    'ratingElse' => $result2['ratingElse']
                                ];

                                array_push($neighborArray, $neighborData);
                            }
                        }
                    }
            }

            if($neighborArray != null){

                $neighborDataArray = [
                    'name' => $result['name'],
                    'neighbor' => $neighborArray
                ];
    
                array_push($arrayNeighborAll, $neighborDataArray);
                
            }
            
        }

        return $arrayNeighborAll;
    }
    //end menentukan neighbor

    //rumus prediksi
    function prediction($arrayNeighborAll){

        $arrayPredictionAll = Array();
        foreach($arrayNeighborAll as $result){

            $arrayPredictionData = Array();
            foreach($result['neighbor'] as $result2){
                
                $predictionData = Array();
                foreach($result2['ratingElse'] as $key => $value){
                    $prediction['key'] = $key;
                    $prediction['value'] = $value*$result2['similarity'];
                    $prediction['similarity'] = $result2['similarity'];
                    array_push($predictionData, $prediction);
                }

                array_push($arrayPredictionData, $predictionData);
            }

            $userDataArray = Array();
            $userData = Array();
            $cekData = Array();
            foreach($arrayPredictionData as $result3){
     
                foreach($result3 as $result4){
                    if(array_key_exists($result4['key'], $cekData)){
                        if(count($userDataArray) > 0){
                            foreach($userDataArray as $key2 => $value2){
                                if(array_key_exists($result4['key'], $userDataArray[$key2])){
                                    if($userDataArray[$key2][$result4['key']] == $result4['key']){
                                        $userDataArray[$key2][$result4['key'].'Val'] = $result4['value']+$userDataArray[$key2][$result4['key'].'Val'];
                                        $userDataArray[$key2][$result4['key'].'Sim'] = $result4['similarity']+$userDataArray[$key2][$result4['key'].'Sim'];
                                    }
                                }
                            }
                        }
                    }else{
                        if(count($userDataArray) > 0){
                            foreach($userDataArray as $key2 => $value2){

                                if(array_key_exists($result4['key'], $userDataArray[$key2])){
                                    if($userDataArray[$key2][$result4['key']] == $result4['key']){
                                        unset($userData['key']);
                                        unset($userData[$result4['key']]);
                                        unset($userData[$result4['key'].'Val']);
                                        unset($userData[$result4['key'].'Sim']);
                                        $userDataArray[$key2][$result4['key'].'Val'] = $result4['value']+$userDataArray[$key2][$result4['key'].'Val'];
                                        $userDataArray[$key2][$result4['key'].'Sim'] = $result4['similarity']+$userDataArray[$key2][$result4['key'].'Sim'];
                                    }
                                }else{
                                    $cekData[$result4['key']] = $result4['key'];
                                    unset($userData);
                                    $userData['key'] = $result4['key'];
                                    $userData[$result4['key']] = $result4['key'];
                                    $userData[$result4['key'].'Val'] = $result4['value'];
                                    $userData[$result4['key'].'Sim'] = $result4['similarity'];
                                    array_push($userDataArray, $userData);
                                    break;
                                }
                                
                            }
                        }else{
                            $cekData[$result4['key']] = $result4['key'];
                            unset($userData);
                            $userData['key'] = $result4['key'];
                            $userData[$result4['key']] = $result4['key'];
                            $userData[$result4['key'].'Val'] = $result4['value'];
                            $userData[$result4['key'].'Sim'] = $result4['similarity'];
                            array_push($userDataArray, $userData);
                        }
                    }
                }

            }
            
            $predictionFinal = Array();
            foreach($userDataArray as $result5){
                $predic['user'] = $result5['key'];
                $predic['rating'] = round($result5[$result5['key'].'Val']/$result5[$result5['key'].'Sim']);
                array_push($predictionFinal, $predic);
                unset($predic);
            }
            
            // die;
            $predictionArray = [
                'name' => $result['name'],
                'user' => $predictionFinal
            ];

            array_push($arrayPredictionAll, $predictionArray);
        }

        return $arrayPredictionAll;
    }
    //end rumus prediksi

    //end Pearson Correlation
    
//=================================SECTION TESTING==================================================
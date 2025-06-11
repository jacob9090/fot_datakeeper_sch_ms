if (move_uploaded_file($_FILES["complain_image"]["tmp_name"], $target_file)) {
        // Insert data into the table
        $sql = "INSERT INTO complainanttbl (case_date, case_id, comp_name, tel, occupation, age, gender, addrs, region, district, loc, case_type, complain_image, diaryofaction)
                VALUES ('$case_date', '$case_num', '$comp_name', '$tel', '$occupation', '$age', '$gender', '$addrs', '$region', '$district', '$loc', '$case_type', '$complain_image', '$diaryofaction')";

        if ($conn->query($sql) === TRUE) {


        $api_key = 'zbe381nb)ahz9q7cc3xr3!8erwc5xmb7yz!sp#)n#)d@l3ljk0fxp9@iba0c(av@';
        $api_secret = 'KINGSCOB';
        $sms_text = 'Hi, ' . $comp_name . ' Your complaint lodged at GPS you will be contacted for further investigation.';

        $sms_url = 'https://sms.nalosolutions.com/smsbackend/Nal_resl/send-message/'; // Replace with your SMS API URL
        $sms_data = array(
            'key' => $api_key,
            'sender_id' => $api_secret,
            'msisdn' => $tel,
            'message' => $sms_text
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sms_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($sms_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $sms_response = curl_exec($ch);
        curl_close($ch);



            echo "<script>alert('Case successfully added')</script>";
                            echo "<script>window.open('caseview.php','_self')</script>";

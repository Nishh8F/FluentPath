<?php
require_once __DIR__ . '/config.php';

try {
    $conn = getDBConnection();
    echo "<h3>Connected to Database Successfully</h3>";

    // 1. Check if the topic column exists, if not, add it.
    $checkColumn = $conn->query("SHOW COLUMNS FROM phrases LIKE 'topic'");
    if ($checkColumn->rowCount() === 0) {
        $conn->exec("ALTER TABLE phrases ADD COLUMN topic VARCHAR(50) DEFAULT 'General'");
        echo "<p>Added 'topic' column to the 'phrases' table.</p>";
    }

    // 2. Check if the example column exists, if not, add it.
    $checkExampleColumn = $conn->query("SHOW COLUMNS FROM phrases LIKE 'example'");
    if ($checkExampleColumn->rowCount() === 0) {
        $conn->exec("ALTER TABLE phrases ADD COLUMN example VARCHAR(255) DEFAULT NULL");
        echo "<p>Added 'example' column to the 'phrases' table.</p>";
    }

    $examples = [
        1 => "Malam ni demo make gapo tu?", 
        2 => "Lewat malam ni mung nak gi mano?",
        3 => "Ambo tok se make nasik dah.", 
        4 => "Ore la ni jange duk koya sunggoh.",
        5 => "Ambo keno kelik awal rini ado kijo.",
        6 => "Takpo, hal ni bereh bo.",
        7 => "Jange duk denga, dio tu kecek supo musoh.",
        8 => "Bakpo lagu tu pulok jadinnyo?",
        9 => "Bilo takdok pitih, nyo gi koya sapa tak kenal ore.",
        10 => "Duk sembang lebat pah ceroh tok abih.",
        11 => "Bini dio bujuk sokmo kalu tok beli bare.",
        12 => "Maing bola pete tadi ado hok ngelat.",
        13 => "Dio ngalik tengok tibi sapa tok dengar ore panggil.",
        14 => "Budak duo ore tu gocoh dale kelas.",
        15 => "Ambo tok sey masuk campur hal demo.",
        16 => "Budo tu tido nyenak sunggoh atah kusi.",
        17 => "Jatuh motor ritu guling bating dale parit.",
        18 => "Abe long duk gomo nge adik dio.",
        19 => "Esok kito gi bekwoh rumoh Pok Ali.",
        20 => "Ambo lapar perut, nok make nasik kerabu.",
        21 => "Beli tepon baru seminggu, nyo pecoh dohh.",
        22 => "Mok beli kelubung baru kaler meroh.",
        23 => "Gege sunggoh budak-budak ni maing.",
        24 => "Bilo cikgu masuk, dale kelas terus senyap tup.",
        25 => "Anak daro tu gelenyar sunggoh nampak jate.",
        26 => "Peyong ambo gelebek keno angin kuca.",
        27 => "Gohek basikal sapa peluh sakan.",
        28 => "Ambo getik sunggoh tengok peranga dio.",
        29 => "Bising sunggoh bunyik moto tu.",
        30 => "Kalu dema, jange maghi kijo.",
        
        61 => "Ate, nak ke mana lekas-lekas ni?",
        62 => "Kome nak gi nengok wayang ke?",
        63 => "Mike dah mentedarah ke belum?",
        64 => "Teman nak pegi poken jap.",
        65 => "Bio lando je kain baju tu, karang teman lipat.",
        66 => "Teman nak kabo, ayor nyor ni sedap benor.",
        67 => "Ghabe laju sikit naik bukit tu.",
        68 => "Batuk sampa kuor gelema.",
        69 => "Hambo abis nasik ke lante.",
        70 => "Bilik komer ni kecah benor.",
        71 => "Lebuh entah ke mana kuncinya.",
        72 => "Moh kita lepak kedai mamak.",
        73 => "Anjing tu ngeboh dari tadi.",
        74 => "Baju teman ngeronyot sebab tak gosok.",
        75 => "Dia tu piut sultan kekdahnya.",
        76 => "Seman rasa perut teman ni.",
        77 => "Budak ni tekey benor kalau dijorit.",
        78 => "Yop teman kerja kat KL.",
        79 => "Yeop teman baru balik mancing.",
        80 => "Yong tolong masakkan gulai petang ni.",
        81 => "Alang selalu tolong cuci kereta.",
        82 => "Andak baru habis sekolah.",
        83 => "Anjang suka main bola.",
        84 => "Uteh duduk asrama sekarang.",
        85 => "Itam kerja kat bank.",
        86 => "Teh pandai masak.",
        87 => "Su selalu bawak mak gi pasar.",
        88 => "Minum ayer kosong banyak-banyak.",
        89 => "Ate, idak le teman nengoknya.",
        90 => "Baju raya teman warna merah tahun ni.",
        
        91 => "Macam mana ni boleh rosak pulak?",
        92 => "Game baru ni boleh tahan gempak jugak.",
        93 => "Malam ni free tak? Jom lepak kedai mamak.",
        94 => "Kesian gila budak tu jatuh basikal.",
        95 => "Wei, kau dah makan ke belum?",
        96 => "Hujung minggu ni nak pergi mana?",
        97 => "Takpe lah, aku buat sendiri je.",
        98 => "Pemandangan kat sini cantik gila do.",
        99 => "Cerita ni bosan do, tukar channel lain.",
        100 => "Serius ah kau nak berhenti kerja?",
        101 => "Jangan percaya, dia tipu do.",
        102 => "Awek dia cantik gila.",
        103 => "Pakwe dia bawak kereta sport.",
        104 => "Kantoi ponteng kelas semalam.",
        105 => "Fuyoo, besarnya rumah kau!",
        106 => "Baju kau harini nampak gerek ah.",
        107 => "Exam tadi kacang je, boleh pass punya.",
        108 => "Mamat tu poyo lebih je, padahal takde apa.",
        109 => "Jangan dengar sangat, dia tu sembang kari je.",
        110 => "Malam ni kita tapau McD je lah.",
        111 => "Jom tengok bola kat kedai mamak.",
        112 => "Bos, teh tarik kurang manis satu!",
        113 => "Aku cincai je, makan mana-mana pun takpe.",
        114 => "Aku tak nak naik roller coaster tu, aku gayat.",
        115 => "Kau ni dari tadi merapu je, cakap betul-betul lah.",
        116 => "Budak tu memang skema sejak sekolah dulu.",
        117 => "Bapak ah, mahalnya harga tiket ni!",
        118 => "Idea kau ni memang cun habis.",
        119 => "Dey, pinjam pen kejap.",
        120 => "Wei, kau nak pergi mana tu?",
        
        31 => "Laju sikik, mung makang dok dang kawan tu.",
        32 => "Berat ngoti dia nangung guni tu.",
        33 => "Awal lagi ni, mung nak gi mane?",
        34 => "Ambe dok sior nok ikut gi pasar.",
        35 => "Guane gamok perikse mu tadi?",
        36 => "Rumoh dia bapok goh besar.",
        37 => "Dia tu skmo je makang awang.",
        38 => "Ambe dok wahi tengok cerite hantu.",
        39 => "Beliang ambe tengok ulat tu.",
        40 => "Mu ni dok cakne langsung hal orang.",
        41 => "Anok dia derik ngoti kat sekolah.",
        42 => "Kite kene lalu ghetok panjang tu.",
        43 => "Baju ni bau hapok, basuhang dok kering.",
        44 => "Budak ni kerah keng, dok dang ajar.",
        45 => "Ikan ni kecik nung, dok rok makang.",
        46 => "Tilam ni lembek ping, sedap tido.",
        47 => "Air teh ni manis leting, potong kaki.",
        48 => "Ubat ni pahit lepe, dok mboh ambe makang.",
        49 => "Asang keping ni masang rebang.",
        50 => "Sambal ni pedah bereng, meleleh ayor mate.",
        51 => "Malam ni cuace sejuk ketor.",
        52 => "Kuah ni tawor hebe, bubuh garam sikik.",
        53 => "Budak-budak ni maing bising gile.",
        54 => "Ambe dok mboh gi situ lagi dah.",
        55 => "Kene katok ngoti mu kalu maghi lek ni.",
        56 => "Tayar muto tu musing ligat.",
        57 => "Baju dia ngonte takdok org lipak.",
        58 => "Mak ngosok baju untuk adik gi skolah.",
        59 => "Anok dia baru pandai ngesot.",
        60 => "Ambe lapar teh, jom gi cari makang.",
        
        121 => "Awat lagu tu hang buat ke dia?",
        122 => "Lewat dah ni, hang nak pi mana?",
        123 => "Kerja ni asyik pi mai pi mai tang tu ja.",
        124 => "Minta lima kupang nak beli ais kacang.",
        125 => "Habaq mai kat aku sapa yang buat.",
        126 => "Anjing tu ligan aku sampai hujung kampung.",
        127 => "Cemuih sungguh aku tengok muka hang.",
        128 => "Budak ni ketegaq tak mau dengaq kata.",
        129 => "Dia dok mengekek gelak dari tadi.",
        130 => "Petang ni jom kayuh gerek pi taman.",
        131 => "Toksah dok peduli la cakap orang.",
        132 => "Kerja hang ni loglaq sungguh, habis tumpah.",
        133 => "Hang jangan dok buat kelolo masa serius ni.",
        134 => "Hingaq sungguh budak-budak ni main bola.",
        135 => "Tolong ambik camca tu sat, nak makan nasi.",
        136 => "Palo aku sakit berdenyut-denyut ni.",
        137 => "Ralit sangat dia main game sampai tak dengar.",
        138 => "Budak tu dah sebik mulut nak menangis.",
        139 => "Cek tak mau makan lagi, kenyang.",
        140 => "Hang nak ikut aku pi kedai dak?",
        141 => "Depa dah pi Penang semalam.",
        142 => "Awang tu anak sapa, segak sungguh.",
        143 => "Baju ni kodi, beli kat pasar malam ja.",
        144 => "Koman sangat kualiti barang ni.",
        145 => "Hang dok melengung apa tu, fikir masa depan ka?",
        146 => "Punggah, habis basah baju jemuran hujan turun.",
        147 => "Orang tua tu dok raban apa entah sorang-sorang.",
        148 => "Hujan renyai ja, tak basah lencun pun.",
        149 => "Sakan hang shopping baju raya tahun ni.",
        150 => "Berlemuih muka hang makan coklat.",
        
        181 => "Привет! Как дела?",
        182 => "Большое спасибо за вашу помощь.",
        183 => "Дайте мне это, пожалуйста.",
        184 => "Извините, где здесь выход?",
        185 => "Здравствуйте, рад вас видеть.",
        186 => "Привет, как прошли выходные?",
        187 => "Уже поздно, до свидания!",
        188 => "Да, я с вами полностью согласен.",
        189 => "Нет, я не хочу туда идти.",
        190 => "Меня зовут Анна.",
        191 => "Извините, как вас зовут?",
        192 => "Я не понимаю, повторите пожалуйста.",
        193 => "Извините, вы говорите по-английски?",
        194 => "Скажите, где туалет?",
        195 => "Сколько это стоит?",
        196 => "Счет, пожалуйста.",
        197 => "Я люблю тебя всем сердцем.",
        198 => "Очень приятно с вами познакомиться.",
        199 => "Подскажите, где находится метро?",
        200 => "Помогите! Мне нужна помощь!",
        201 => "Срочно, мне нужен врач!",
        202 => "Доброе утро! Как спалось?",
        203 => "Добрый день, мы можем начать встречу?",
        204 => "Добрый вечер, как прошел ваш день?",
        205 => "Спокойной ночи, сладких снов.",
        206 => "Хорошо, мы договорились.",
        207 => "Плохо, что так получилось.",
        208 => "Этот торт очень вкусный.",
        209 => "Я из Москвы.",
        210 => "Извините, который час?",
        
        151 => "Hello, how are you doing today?",
        152 => "Excuse me, do you know what time it is?",
        153 => "Where is the bathroom located?",
        154 => "I would like to order a pizza, please.",
        155 => "How much does this cost in total?",
        156 => "Could you help me please with these bags?",
        157 => "I don't understand the instructions clearly.",
        158 => "Please speak slowly so I can translate.",
        159 => "Nice to meet you, my name is Sarah.",
        160 => "Thank you very much for your generous gift.",
        161 => "You are welcome to join us for dinner.",
        162 => "Excuse me, I need to get past you.",
        163 => "I am sorry for being so late.",
        164 => "Yes, please, I would love some more tea.",
        165 => "No, thank you, I am already full.",
        166 => "What is your name, if I may ask?",
        167 => "My name is John Doe.",
        168 => "Where are you from originally?",
        169 => "I am from New York City.",
        170 => "Do you speak English well?",
        171 => "I need a doctor right away!",
        172 => "There has been a robbery, call the police!",
        173 => "Where is the hospital around here?",
        174 => "I am lost, can you show me the map?",
        175 => "Can I have the bill, we are ready to leave.",
        176 => "Where is the train station from this street?",
        177 => "I love this place, it's so beautiful.",
        178 => "What a beautiful day to go to the park!",
        179 => "See you later, have a safe trip home.",
        180 => "Have a good day at work today!"
    ];

    // 3. Fetch all phrases to update topic and examples
    $stmt = $conn->query("SELECT id, correct_answer FROM phrases");
    $phrases = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updateStmt = $conn->prepare("UPDATE phrases SET topic = :topic, example = :example WHERE id = :id");
    
    $foodKeywords = ['eat', 'eating', 'hungry', 'sweet', 'bitter', 'sour', 'spicy', 'food', 'drink', 'feast', 'cook', 'coconut', 'water', 'spoon', 'spoilt', 'taste', 'delicious'];
    $travelKeywords = ['go', 'going', 'where', 'walk', 'bridge', 'bicycle', 'car', 'travel', 'journey', 'come', 'coming', 'climb', 'chase', 'run', 'fast', 'slow'];
    $greetingKeywords = ['how are', 'what are', 'hello', 'good', 'morning', 'night', 'doing', 'things', 'you', 'me', 'i', 'we', 'they', 'he', 'she', 'why', 'tell', 'yes', 'no', 'boy', 'man', 'girl', 'woman', 'brother', 'sister', 'child', 'children'];
    
    $counts = ['Greetings' => 0, 'Food' => 0, 'Travel' => 0, 'General' => 0];

    foreach ($phrases as $phrase) {
        $meaning = $phrase['correct_answer'];
        $topic = 'General';
        
        // Check Food
        foreach ($foodKeywords as $kw) {
            if (preg_match("/\b" . preg_quote(trim($kw), '/') . "\b/i", $meaning)) {
                $topic = 'Food';
                break;
            }
        }
        
        // Check Travel
        if ($topic === 'General') {
            foreach ($travelKeywords as $kw) {
                if (preg_match("/\b" . preg_quote(trim($kw), '/') . "\b/i", $meaning)) {
                    $topic = 'Travel';
                    break;
                }
            }
        }
        
        // Check Greetings
        if ($topic === 'General') {
            foreach ($greetingKeywords as $kw) {
                if (preg_match("/\b" . preg_quote(trim($kw), '/') . "\b/i", $meaning)) {
                    $topic = 'Greetings';
                    break;
                }
            }
        }
        
        $example = isset($examples[$phrase['id']]) ? $examples[$phrase['id']] : "Use this phrase when you want to say: " . $meaning;
        
        // Update DB
        $updateStmt->execute([':topic' => $topic, ':example' => $example, ':id' => $phrase['id']]);
        $counts[$topic]++;
    }
    
    echo "<h3>Topics and Localized Examples Successfully Synced on Azure!</h3>";
    echo "<ul>";
    foreach($counts as $category => $count) {
        echo "<li><strong>$category:</strong> $count phrases updated</li>";
    }
    echo "</ul>";
    
    echo "<p><em>You can now safely delete this file from your server.</em></p>";
    
} catch(PDOException $e) {
    echo "<h3>Error</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}

<?php

if (mysqli_connect_errno()) {
    echo "连接至 MySQL 失败: " . mysqli_connect_error();
}

$conn = mysqli_connect('localhost', 'root', '', '112dba11');
mysqli_query($conn, 'SET NAMES utf8');
mysqli_query($conn, 'SET CHARACTER_SET_CLIENT=utf8');
mysqli_query($conn, 'SET CHARACTER_SET_RESULTS=utf8');

// 定义每页显示的行数和每行的卡片数
$rowsPerPage = 1; // 每页一行
$cardsPerRow = 4; // 每行四个卡片
$cardsPerPage = $rowsPerPage * $cardsPerRow; // 每页共四个卡片

// 计算总页数
$sqlCount = "SELECT COUNT(*) AS total FROM animal";
$resultCount = mysqli_query($conn, $sqlCount);
$rowCount = mysqli_fetch_assoc($resultCount);
$totalPages = ceil($rowCount['total'] / $cardsPerPage);

// 获取当前页码
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $currentPage = (int) $_GET['page'];
} else {
    $currentPage = 1;
}

// 计算用于SQL LIMIT子句的起始位置
$start = ($currentPage - 1) * $cardsPerPage;

$animals = array(); // 初始化数组来存储动物信息

// 根据当前页码获取动物信息
$sqlAll = "SELECT animal.*, park.*, reside.*
           FROM animal 
           INNER JOIN (park INNER JOIN reside ON park.parkId = reside.parkId) 
           ON animal.animalId = reside.animalId
           LIMIT $start, $cardsPerPage;";

$resultAll = mysqli_query($conn, $sqlAll);

if ($resultAll) {
    while ($row = mysqli_fetch_assoc($resultAll)) {
        $animals[] = $row; // 将每个结果添加到数组中
    }
}

// 如果有搜索条件，重新定义$animals数组，仅包含符合条件的结果
if (isset($_GET["animal_key"])) {
    $key1 = $_GET["animal_key"];
    if ($key1 != "") {
        $sql = "SELECT animal.*, park.*, reside.*
                FROM animal 
                INNER JOIN (park INNER JOIN reside ON park.parkId = reside.parkId) 
                ON animal.animalId = reside.animalId
                WHERE animal.ch_name LIKE '%$key1%' OR park.name LIKE '%$key1%'
                LIMIT $start, $cardsPerPage;";

        $result = mysqli_query($conn, $sql);

        $animals = array(); // 重置数组

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $animals[] = $row; // 将每个搜索结果添加到数组中
            }
        }
    }
}


// 在这里定义额外信息数组
$additional_info = array(
    '1' => '我是山羌',
    '2' => '我是台灣野豬',
    '3' => '我是梅花鹿',
    '4' => '我是台灣獼猴',
    '5' => '我是穿山甲',
    '6' => '我是雲豹',
    '7' => '我是歐亞水獺',
    '8' => '我是臺灣黑熊',
    '9' => '我是臺灣長鬃山羊',
    '10' => '我是迷你馬',
    '11' => '我是驢子',
    '12' => '我是狐猴',
    '13' => '我是長鼻浣熊',
    '14' => '我是狐蒙',
    '15' => '我是浣熊',
    '16' => '我是羊駝',
    '17' => '我是紅鶴',
    '18' => '我是白手長臂猿',
    '19' => '我是熊貓',
    '20' => '我是水豚',
    '21' => '我是小爪水獺',
    '22' => '我是大食蟻獸',
    '23' => '我是黑冠松鼠猴',
    '24' => '我是馬來貘',
    '25' => '我是指猴',
    '26' => '我是大長臂猿',
    '27' => '我是人猿',
    '28' => '我是花豹',
    '29' => '我是大犀鳥',
    '30' => '我是亞洲象',
    '31' => '我是孟加拉虎',
    '32' => '我是斑馬',
    '33' => '我是長頸鹿',
    '34' => '我是伊蘭羚羊',
    '35' => '我是獅子',
    '36' => '我是白犀牛',
    '37' => '我是黑猩猩',
    '38' => '我是河馬',
    '39' => '我是斑哥羚',
    '40' => '我是大猩猩',
    '41' => '我是非洲象',
    '42' => '我是東非狒狒',
    '43' => '我是鵜鶘',
    '44' => '我是雁鴨',
    '45' => '我是鳩鴿',
    '46' => '我是紅環',
    '47' => '我是黑面琵鷺',
    '48' => '我是鷹鷲',
    '49' => '我是鸚鵡',
    '50' => '我是鶴',
    '51' => '我是蒙古野馬',
    '52' => '我是蘇卡達象龜',
    '53' => '我是美洲野牛',
    '54' => '我是馬來長吻鱷',
    '55' => '我是河狸',
    '56' => '我是灰狼',
    '57' => '我是美洲山獅',
    '58' => '我是小貓熊',
    '59' => '我是棕熊',
    '60' => '我是企鵝',
    '61' => '我是無尾熊',
    '62' => '我是弓角羚羊',
    '63' => '我是非洲野驢',
    '64' => '我是雙峰駱駝',
    '65' => '我是單峰駱駝',
    '66' => '我是食火雞',
    '67' => '我是大灰袋鼠',
    // ...为每个动物继续添加信息
);

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>動物專區</title>
    <style>
      body {
        margin: 0;
        font-family: "Arial", sans-serif;
        background-color: #ffffff;
      }
      .flex {
        display: flex;
      }
      .column {
        flex-direction: column;
      }
      .container {
        position: relative;
        height: 500px;
      }

      .navbar {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        display: flex;
        align-items: center;
        background-color: rgba(107, 104, 104, 0.5); /* 暗化效果 */
        padding-left: 80px;
      }

      .nav-link {
        color: #000;
        text-decoration: none;
      }

      .background-image {
        height: 100%;
        width: 100%;
      }
      #scroll-to-top {
        display: none;
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 60px;
        height: 60px;
        font-size: 20px;
        cursor: pointer;
      }

      #scroll-to-top:hover {
        background-color: #0056b3;
      }

      #scroll-to-top span {
        display: block;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
      }

      select,
      input[type="text"],
      input[type="submit"] {
        padding: 5px;
        margin: 5px;
        border: 1px solid #ccc;
        border-radius: 5px;
      }

      input[type="submit"] {
        background-color: #007bff;
        color: #fff;
        cursor: pointer;
      }

      input[type="submit"]:hover {
        background-color: #0056b3;
      }

      .resize-image {
        max-width: 200px; /* 最大宽度 */
        max-height: 200px; /* 最大高度 */
        width: auto; /* 保持宽高比 */
        height: auto; /* 保持宽高比 */
      }
       
      /* 添加的横向布局样式 */
         .animal-container {
            display: flex;
            flex-wrap: wrap; /* 如果有太多元素，它们会换行显示 */
            justify-content: center; /* 居中排列 */
            gap: 10px; /* 元素之间的间隔 */
        }

        .animal-card {
            border: 1px solid #ccc; /* 边框 */
            padding: 10px;
            border-radius: 10px; /* 圆角边框 */
            max-width: 200px; /* 卡片的最大宽度 */
            text-align: center; /* 文本居中 */
        }


     


        .modal {
    display: none; /* 默认隐藏 */
    position: fixed; 
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4); /* 半透明背景 */
    z-index: 1000; /* 确保在页面内容之上 */
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto; /* 居中显示 */
    padding: 20px;
    border: 1px solid #888;
    width: 50%; /* 模态框宽度 */
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

/* 分页链接样式 */
.pagination {
    display: flex;
    justify-content: center;
    padding: 20px 0;
}

.pagination a {
    padding: 8px 16px;
    margin: 4px;
    border: 1px solid #ddd;
    text-decoration: none;
    /* 可以添加其他样式，如背景色、边框圆角等 */
}

.pagination a.active {
    background-color: #4CAF50;
    color: white;
    border: 1px solid #4CAF50;
}

.pagination a:hover:not(.active) {
    background-color: #ddd;
}


    </style>
  </head>
  <body>
    <div>
      <div
        class="flex"
        style="
          background-color: rgba(231, 232, 231, 0.5); /* 半透明背景 */
          backdrop-filter: blur(10px); /* 模糊效果 */
          -webkit-backdrop-filter: blur(10px); /* 針對舊版Safari瀏覽器的支持 */
          padding: 20px;
          height: 80px;
          position: fixed;
          top: 0;
          width: 100%;
          z-index: 100;
        "
      >
        <a href="index.html" class="flex nav-link">
          <img
            src="image/head.png"
            width="70px"
            height="70px"
            style="margin-left: 100px; margin-top: 10px"
          />
          <div style="margin-left: 20px; margin-top: 0px">
            <h1>WLLY ZOO</h1>
          </div>
        </a>
      </div>

      <div style="height: 120px"></div>

      <div class="container">
        <div class="navbar">
          <div>☰&nbsp;</div>
          <div class="link">
            <a href="index.html" class="nav-link">首頁</a>
          </div>
          <div>&nbsp;☞&nbsp;</div>
          <div class="link">
            <a href="animal1.php" class="nav-link">動物專區</a>
          </div>
        </div>
        <img src="image/hozoo.png" class="background-image" />
      </div>

      <div 
      class="flex column" 
      style="
      align-items: center; 
      justify-content: center; 
      margin-top: 10px;"
      >
<div>動物查詢</div>
        <div>
         <form action="" method="get">
          <input type="text" name="animal_key" placeholder="輸入名字">
          <input type="submit" >
         </form>
        </div>
      </div>

      <div
        style="
          width: 60%; /* 設定分隔線的長度為容器寬度的80% */
          height: 2px; /* 設定分隔線的厚度 */
          background-color: #d3d3d3; /* 淺灰色 */
          margin: 20px auto; /* 上下邊距20px，自動左右邊距實現居中 */
        "
      ></div>

      <div class="animal-container">
      <!-- 查询结果显示 -->
       <?php foreach ($animals as $animal): ?>
        <div class="animal-card">
         <div><img src="<?php echo $animal['img']; ?>" class="resize-image"></div>
         <div>名字：<?php echo $animal['ch_name']; ?></div>
         <div>❤<?php echo $animal['Popularity']; ?></div>
         <div>園區：<?php echo $animal['name']; ?></div>
         <!-- 点击按钮触发模态框 -->
         <button onclick="openModal('modalAnimal<?php echo $animal['animalId']; ?>')">更多信息</button>
       </div>

      <!-- 模态框结构 -->
       <div id="modalAnimal<?php echo $animal['animalId']; ?>" class="modal">
          <div class="modal-content">
            <span class="close" onclick="closeModal('modalAnimal<?php echo $animal['animalId']; ?>')">&times;</span>
            <!-- 使用animal['animalId']从数组中获取额外信息 -->
            <h2><?php echo $animal['ch_name']; ?>的介绍</h2>
            <p><?php echo $additional_info[$animal['animalId']]; ?></p>
            <!-- 其他信息也可以在这里添加 -->
          </div>
        </div>
       <?php endforeach; ?>
       </div>

        <!-- 分页链接 -->
    <div class="pagination" style="margin-top: 80px">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" <?php if ($i == $currentPage) echo 'class="active"'; ?>>
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>

       <div style="height: 200px; background-color: #e7e8e7; ">
        <div
          class="flex"
          style="padding: 20px; justify-content: center; margin-top: 80px"
        >
          <div class="flex">
            <div>
              <img src="image/head.png" width="70px" height="70px" />
            </div>
            <div style="margin-left: 5px; margin-top: 20px; margin-left: 10px">
              WLLY ZOO
            </div>
          </div>
          <div class="column" style="margin-left: 300px">
            <div>地址：116台北市文山區新光路二段30號</div>
            <div>電話：0229382300</div>
            <div>更新日期 2023-11-17</div>
          </div>
        </div>
        <div>
          <hr
            style="border-color: black; border-width: 2px; border-style: solid"
          />
          <div class="flex" style="justify-content: center">
            Copyright © 2023 Zoo Inc. All rights reserved.
          </div>
        </div>

    </div>

     <button id="scroll-to-top" onclick="scrollToTop()">
      <span>TOP</span>
    </button>
    <script> 

      // 當用戶滾動頁面時，顯示或隱藏按鈕
      window.onscroll = function () {
        var button = document.getElementById("scroll-to-top");
        if (
          document.body.scrollTop > 20 ||
          document.documentElement.scrollTop > 20
        ) {
          button.style.display = "block";
        } else {
          button.style.display = "none";
        }
      };

      // 當按鈕被點擊時，滾動到頂部
      function scrollToTop() {
        document.body.scrollTop = 0; // 對於一些瀏覽器
        document.documentElement.scrollTop = 0; // 對於現代瀏覽器
      }
  


     // 打开模态框
function openModal(modalId) {
    document.getElementById(modalId).style.display = "block";
}

// 关闭模态框
function closeModal(modalId) {
    document.getElementById(modalId).style.display = "none";
}

    </script>
</body>
</html>
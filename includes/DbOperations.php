<?php
class DbOperations
{

     private $con;

     function __construct()
     {

          require_once dirname(__FILE__) . '/DbConnect.php';
          include_once dirname(__FILE__) . '/Constants.php';

          $db = new DbConnect();

          $this->con = $db->connect();
     }


     /* CREATE DATABASE */

     public function createUser($uid, $referral, $deviceID)
     {

          $stmt = $this->con->prepare("INSERT INTO `users` (`uid`, `referral`, `deviceID`) VALUES (?, ?, ?);");
          $stmt->bind_param("sss", $uid, $referral, $deviceID);

          if ($stmt->execute()) {
               $stmt->close();
               return 1;
          } else {
               $stmt->close();
               return 2;
          }
     }

     public function getData($uid)
     {
          if (!$this->isDataExists($uid)) {
               return json_encode(['code' => 102, 'message' => 'USER NOT FOUND']);
          }

          $stmt = $this->con->prepare("SELECT
          u.uid, u.points, u.referral, u.ban, u.totalReferrals, u.referredBy,
          u.referralToday, u.totalWatched, u.streak, u.streakClaimed, u.maxStreak,
          u.tasks, u.spinTime, u.spinCount, u.luckyNum, u.requests, u.adsWatched,
          u.payoutLock, u.videoWatched, a.dailyReset, a.number, a.luckyNumber
      FROM users u
      JOIN admin a
      WHERE uid = ? AND a.id = 1;");
          $stmt->bind_param("s", $uid);
          if ($stmt->execute()) {
               $result = $stmt->get_result();
               $row = $result->fetch_assoc();
               $stmt->close();
               // Initialize the array with zeros
               $arr = array_fill(0, 7, 0);
               // Check if streakClaimed is greater than streak
               if ($row['streakClaimed'] <= $row['streak']) {
                    for ($i = -1; $i < $row['streak']; $i++) {
                         if ($i < $row['streakClaimed']) {
                              $arr[$i + 1] = 2;
                         } elseif ($i == $row['streakClaimed']) {
                              $arr[$i + 1] = 1;
                         }
                    }
               }

               $row = array_merge($row, [
                    'streakArr' => $arr,
                    'tasks' => [json_decode($row['tasks'])]
               ]);

               return json_encode($row);
          } else {
               $stmt->close();
               return NULL;
          }
     }

     public function withdrawal($uid, $points, $aid)
     {

          $stmt = $this->con->prepare("UPDATE `users` SET `Coins` = Coins - ?, `Aid` = ? WHERE `users`.`UID` = ?");

          $stmt->bind_param("iss", $points, $aid, $uid);

          if ($stmt->execute()) {
               return 1;
          } else {
               return 2;
          }
     }


     public function referral($uid, $referral_opposite)
     {
          //check for can referral ?
          $check = $this->con->prepare("SELECT UID FROM users WHERE UID = ? AND referredBy IS NULL AND referral != ?");
          $check->bind_param("ss", $uid, $referral_opposite);
          $check->execute();
          $check->store_result();
          if ($check->num_rows > 0) {
               $check->close();
               $stmt = $this->con->prepare("SELECT UID FROM `users` WHERE `referral` = ?");
               $stmt->bind_param("s", $referral_opposite);
               $stmt->execute();
               $stmt->store_result();
               if ($stmt->num_rows > 0) {
                    $stmt4 = $this->con->prepare("UPDATE `users` SET `points` = `points` + 700, totalReferrals = totalReferrals + 1, referralToday = referralToday + 1 WHERE `users`.`referral` = ?;");
                    $stmt4->bind_param("s", $referral_opposite);
                    if ($stmt4->execute()) {
                         $stmt1 = $this->con->prepare("UPDATE `users` SET `points` = `points` + 500 WHERE `users`.`UID` = ?;");
                         $stmt1->bind_param("s", $uid);
                         if ($stmt1->execute()) {
                              $stmt2 = $this->con->prepare("UPDATE `users` SET `referredBy` = (SELECT UID FROM `users` WHERE `referral` = ?) WHERE `users`.`UID` = ?;");
                              $stmt2->bind_param("ss", $referral_opposite, $uid);
                              if ($stmt2->execute()) {
                                   $stmt4->close();
                                   $stmt2->close();
                                   $stmt1->close();
                                   $stmt->close();
                                   return ['code' => 101, 'message' => 'REFERRAL SUCCESSFUL'];
                              }
                         } else {
                              return ['code' => 102, 'message' => 'USER NOT FOUND'];
                         }
                    } else {
                         return ['code' => 102, 'message' => 'USER NOT FOUND'];
                    }

               } else {
                    return ['code' => 102, 'message' => 'INVALID CODE'];
               }
          } else {
               return ['code' => 102, 'message' => 'ALREADY REFERRED'];
          }
     }


     public function isUserExists($deviceID)
     {
          $stmt = $this->con->prepare("SELECT * FROM users WHERE deviceID=?");
          $stmt->bind_param("s", $deviceID);
          $stmt->execute();
          $stmt->store_result();
          return $stmt->num_rows > 0;
     }

     public function isDataExists($uid)
     {
          $stmt = $this->con->prepare("SELECT * FROM users WHERE uid=?");
          $stmt->bind_param("s", $uid);
          $stmt->execute();
          $stmt->store_result();
          return $stmt->num_rows > 0;
     }

     public function checkAds20($uid)
     {
          $stmt = $this->con->prepare("SELECT * FROM users WHERE uid=? AND adsWatched >= 20");
          $stmt->bind_param("s", $uid);
          $stmt->execute();
          $stmt->store_result();
          return $stmt->num_rows > 0;
     }

     public function checkCoupon($coupon)
     {
          $stmt = $this->con->prepare("SELECT `code` FROM `admin` WHERE `code` LIKE ?");
          $likeParameter = "%$coupon%";
          $stmt->bind_param("s", $likeParameter);
          $stmt->execute();
          $stmt->store_result();
          return $stmt->num_rows <= 0;
     }


     public function checkCouponClaim($uid, $coupon)
     {
          $stmt = $this->con->prepare("SELECT * FROM users WHERE uid=? AND code LIKE ?");
          $likeParameter = "%$coupon%";
          $stmt->bind_param("ss", $uid, $likeParameter);
          $stmt->execute();
          $stmt->store_result();
          return $stmt->num_rows > 0;
          // returns true if already claimed
     }

     public function checkpoints($uid, $value)
     {
          $stmt = $this->con->prepare("SELECT * FROM users WHERE uid=? AND points >= ?");
          $stmt->bind_param("si", $uid, $value);
          $stmt->execute();
          $stmt->store_result();
          return $stmt->num_rows > 0;
     }

     public function validate($hash)
     {
          $stmt = $this->con->prepare("SELECT * FROM requests WHERE reqs=?");
          $stmt->bind_param("s", $hash);
          $stmt->execute();
          $stmt->store_result();
          if ($stmt->num_rows > 0) {
               $stmt->close();
               return true;
          } else {
               $stmt = $this->con->prepare("INSERT INTO requests (reqs) VALUES (?)");
               $stmt->bind_param("s", $hash);
               $stmt->execute();
               $stmt->close();
               return false;
          }

     }

     public function setTime($uid, $time)
     {
          $stmt = $this->con->prepare("UPDATE `users` SET `LastRequest` = ?, requests = requests + 1 WHERE uid=?");
          $stmt->bind_param("is", $time, $uid);
          $stmt->execute();
          $stmt->close();
     }


     // New Code from here -----------------------------------------------------------------

     function addPointsHistory($uid, $amount, $source, $id)
     {
          if (!$uid || !$amount || !$source || !$id) {
               return;
          }

          $stmt = $this->con->prepare("SELECT pointsHistory FROM users WHERE uid=?");

          $stmt->bind_param("s", $uid);

          if ($stmt->execute()) {
               $result = $stmt->get_result();
               $row = $result->fetch_assoc();
               $stmt->close();

               // Decode and update pointsHistory
               $payoutHistory = json_decode($row['pointsHistory'], true)['history'];

               $newEntry = array("amount" => $amount, "source" => $source, "id" => $id);

               // Append the new entry to the current history
               $currentHistory['history'][] = $newEntry;

               // Update the pointsHistory in the database
               $stmt = $this->con->prepare("UPDATE users SET pointsHistory=? WHERE uid=?");
               $history = json_encode($currentHistory);
               $stmt->bind_param("ss", $history, $uid);
               $stmt->execute();
               $stmt->close();
          } else {
               return NULL;
          }
     }

     // Routes from here -------------------------------------------------------------------

     public function getVideo()
     {

          $my_vid = [
               [
                    'title' => 'Drawing an elephant using letter G',
                    'description' => 'In this video, I will be showing you how to create an elephant using letter G Very simple to learn, amazing way, coloring using brush. | VERY EASY 🔥.',
                    'videoID' => 0,
                    'uid' => 123,
                    'visibility' => 0,
                    'link' => '73DM4KS1GYY'
               ]
          ];

          $stmt = $this->con->prepare("SELECT * FROM videos ORDER BY RAND()");

          if ($stmt->execute()) {
               $result = $stmt->get_result();
               $rows = $my_vid;
               while ($row = $result->fetch_assoc()) {
                    $rows[] = $row;
               }
               $stmt->close();
               return json_encode($rows);
          } else {
               $stmt->close();
               return NULL;
          }
     }

     public function getChannels($uid)
     {
          $stmt = $this->con->prepare("
          SELECT *
          FROM subs
          WHERE subs.id NOT IN (
              SELECT DISTINCT id
              FROM users
              WHERE uid = ?
                AND FIND_IN_SET(subs.id, users.subs) > 0
          );          
");
          $stmt->bind_param("s", $uid);

          if ($stmt->execute()) {
               $result = $stmt->get_result();
               $rows = [];
               while ($row = $result->fetch_assoc()) {
                    $rows[] = $row;
               }
               $stmt->close();
               return json_encode($rows);
          } else {
               $stmt->close();
               return NULL;
          }
     }

     public function checkAlreadySubscribed($userId, $uid)
     {
          $stmt = $this->con->prepare("
        SELECT COUNT(*) AS count
        FROM users
        WHERE subs IS NOT NULL AND FIND_IN_SET(?, subs) > 0 AND uid = ?
    ");

          $stmt->bind_param("is", $userId, $uid);

          if ($stmt->execute()) {
               $result = $stmt->get_result();
               $row = $result->fetch_assoc();
               $stmt->close();

               // If count is greater than 0, the user is already subscribed
               return $row['count'] > 0;
          } else {
               $stmt->close();
               return false; // Error occurred
          }
     }



     public function setYoutubeClaim($uid)
     {
          // Execute the update query with a condition
          $stmt = $this->con->prepare("UPDATE users SET points = points + ?, youtubeTime = ?, totalWatched = totalWatched + 1, totalWatchedDaily = totalWatchedDaily + 1 WHERE uid = ? AND (youtubeTime IS NULL OR youtubeTime < ? OR totalWatched > 30)");

          $p = rand(20, 30);
          $currentYoutubeTime = time();
          $futureYoutubeTime = time() + 30;

          $stmt->bind_param("iisi", $p, $futureYoutubeTime, $uid, $currentYoutubeTime);

          if ($stmt->execute()) {
               $stmt->close();
               return 1;
          }

          $stmt->close();
          return 2;
     }

     public function setCodeClaim($uid, $coupon)
     {
          // check valid coupon or not
          if ($this->checkCoupon($coupon)) {
               return 3;
          }

          // check coupon already claimed by user or not
          if ($this->checkCouponClaim($uid, $coupon)) {
               return 4;
          }

          // Revised SQL query
          $stmt = $this->con->prepare("UPDATE users AS u JOIN coupons AS c ON c.code LIKE ? SET u.points = u.points + c.reward, u.code = CONCAT(u.code, ?) WHERE u.uid = ? AND u.code NOT LIKE ?");

          $likeParameter = "%$coupon%";
          $commaParam = ",$coupon";
          $stmt->bind_param("ssss", $likeParameter, $commaParam, $uid, $likeParameter);

          if ($stmt->execute()) {
               $stmt->close();
               return 1;
          }

          $stmt->close();
          return 2;
     }

     public function setAddSubs($uid, $name, $link, $clicks, $reward)
     {
          // check for balance
          $pointssub = $reward * $clicks;
          if ($this->checkpoints($uid, $pointssub)) {
               // Execute the update query with a condition
               $stmt = $this->con->prepare("INSERT INTO `subs`( `uid`, `link`, `valid`, `reward`, `time`, `name`) VALUES (?,?,?,?,?,?)");
               $currentTime = time();
               $stmt->bind_param("ssiiis", $uid, $link, $clicks, $reward, $currentTime, $name);

               if ($stmt->execute()) {
                    $stmt->close();
                    $pointssub = $reward * $clicks;
                    $stmt1 = $this->con->prepare("UPDATE users SET points = points - ? WHERE `uid` = ?");
                    $stmt1->bind_param("is", $pointssub, $uid);
                    $stmt1->execute();
                    $stmt1->close();

                    // add revenue to admin panel
                    $stmt2 = $this->con->prepare("UPDATE admin SET revenue_subs = revenue_subs + ? WHERE 1");
                    $stmt2->bind_param("i", $pointssub);
                    $stmt2->execute();
                    $stmt2->close();

                    return 1;
               }
               $stmt->close();
               return 2;
          } else {
               return 3;
          }


     }

     public function addPointsSubs($uid, $id)
     {
          if ($this->checkAlreadySubscribed($id, $uid)) {
               return 3;
          }
          // Execute the update query with a condition
          $stmt = $this->con->prepare("UPDATE subs SET `clicks` = clicks + 1, valid = valid - 1 WHERE `id` = ?");
          $stmt->bind_param("s", $id);

          if ($stmt->execute()) {
               $stmt->close();
               $updated = "," . $id;
               $stmt1 = $this->con->prepare("UPDATE users SET points = points + (SELECT `reward` FROM `subs` WHERE `id` = ?), subs = CONCAT(subs, ?) WHERE `uid` = ?");
               $stmt1->bind_param("sss", $id, $updated, $uid);
               $stmt1->execute();
               $stmt1->close();

               $stmt2 = $this->con->prepare("DELETE FROM subs WHERE id = ? AND valid <= 0");
               $stmt2->bind_param("s", $id);
               $stmt2->execute();
               $stmt2->close();

               return 1;
          }

          $stmt->close();
          return 2;
     }

     public function getChannel()
     {

          $stmt = $this->con->prepare("SELECT youtubeURL FROM `admin` WHERE ID = 1");

          if ($stmt->execute()) {
               $result = $stmt->get_result();
               $row = $result->fetch_assoc();
               $stmt->close();
               return $row['youtubeURL'];
          } else {
               $stmt->close();
               return NULL;
          }
     }

     public function claimStreak($uid)
     {
          // check streak claimed or not
          $stmt = $this->con->prepare("UPDATE users SET streakClaimed = streak, points = points + CASE streakClaimed WHEN 0 THEN 100 WHEN 1 THEN 150 WHEN 2 THEN 200 WHEN 3 THEN 300 WHEN 4 THEN 350 WHEN 5 THEN 400 WHEN 6 THEN 600 ELSE 0 END, maxStreak = CASE WHEN streak = 6 THEN 1 ELSE maxStreak END WHERE uid = ? AND streakClaimed < streak;");
          $stmt->bind_param("s", $uid);
          $stmt->execute();
          $stmt->store_result();
          if ($stmt->num_rows > 0) {
               $stmt->close();
               return 1;
          } else {
               $stmt->close();
               return 2;
          }

     }

     public function claimTask($uid, $id)
     {
          $conn = $this->con;

          $stmtUser = $conn->prepare("SELECT * FROM users WHERE uid=?");
          $stmtUser->bind_param("s", $uid);
          $stmtUser->execute();
          $resultUser = $stmtUser->get_result();
          $user = $resultUser->fetch_assoc();
          $stmtUser->close();

          $stmtTask = $conn->prepare("SELECT * FROM tasks WHERE taskID=?");
          $stmtTask->bind_param("s", $id);
          $stmtTask->execute();
          $resultTask = $stmtTask->get_result();
          $taskList = $resultTask->fetch_assoc();
          $stmtTask->close();

          $tasks = json_decode($user['tasks'], true);

          if (isset($tasks[$id]) && !$tasks[$id]['claimed']) {
               $taskName = $taskID = '';

               switch ($id) {
                    case '1':
                         if ($user['maxStreak'] !== 0) {
                              $taskName = 'Claim Streak';
                              $taskID = 'claim_streak';
                         }
                         break;

                    case '2':
                         if ($user['referralToday'] >= $taskList['max']) {
                              $taskName = 'Share App Daily';
                              $taskID = 'daily_share';
                         }
                         break;

                    case '3':
                         if ($user['totalWatched'] >= $taskList['max']) {
                              $taskName = 'Watch 20 Videos';
                              $taskID = 'watch_vids';
                         }
                         break;

                    case '4':
                         if ($user['spinCount'] >= $taskList['max']) {
                              $taskName = 'Spin 15 Times';
                              $taskID = 'spin';
                         }
                         break;

                    case '6':
                         if ($user['requests'] >= 3600) {
                              $taskName = 'Use App For 1 Hour';
                              $taskID = 'usage';
                         }
                         break;

                    case '7':
                         $taskName = 'Subscribe YouTube';
                         $taskID = 'youtube_sub';
                         break;

                    case '8':
                         $taskName = 'Follow TikTok';
                         $taskID = 'tiktok';
                         break;

                    default:
                         return ['code' => 403, 'message' => 'INVALID TASK ID'];
               }

               if ($taskName && $taskID) {
                    $tasks[$id]['claimed'] = true;

                    $stmtUpdateUser = $conn->prepare("UPDATE users SET points=points+?, tasks=? WHERE uid=?");
                    $tasks_up = json_encode($tasks);
                    $stmtUpdateUser->bind_param("iss", $taskList['points'], $tasks_up, $uid);
                    $stmtUpdateUser->execute();
                    $stmtUpdateUser->close();

                    $stmtUpdateTask = $conn->prepare("UPDATE tasks SET claimed=claimed+1 WHERE taskID=?");
                    $stmtUpdateTask->bind_param("s", $id);
                    $stmtUpdateTask->execute();
                    $stmtUpdateTask->close();

                    if ($id == '1') {
                         $stmtUpdateMaxStreak = $conn->prepare("UPDATE users SET maxStreak=0 WHERE uid=?");
                         $stmtUpdateMaxStreak->bind_param("s", $uid);
                         $stmtUpdateMaxStreak->execute();
                         $stmtUpdateMaxStreak->close();
                    }

                    return ['code' => 101, 'message' => $taskName . " got " . $taskList['points'] . " Points."];
               } else {
                    return ['code' => 403, 'message' => 'CANNOT CLAIM TASK.'];
               }
          } else {
               return ['code' => 102, 'message' => 'TASK ALREADY CLAIMED.'];
          }

     }

     public function claimSpin($uid, $index)
     {
          // check streak claimed or not
          $stmt = $this->con->prepare("UPDATE users SET points=points+?,spinTime=?,spinCount=spinCount+1,spinCountDaily=spinCountDaily+1 WHERE uid=? AND (spinTime IS NULL OR spinTime < ?)");

          $p = array(10, 12, 15, 20, 5, 30);
          $currentSpinTime = time();
          $futureSpinTime = time() + 900;

          $stmt->bind_param("iisi", $p[$index], $futureSpinTime, $uid, $currentSpinTime);

          if ($stmt->execute()) {
               $stmt->close();
               return ['code' => 101, 'message' => $p[$index] . " Points Added!"];
          }

          $stmt->close();
          return ['code' => 102, 'message' => 'SPIN ALREADY CLAIMED.'];

     }

     public function setLucky($uid, $number)
     {
          // check streak claimed or not
          $stmt = $this->con->prepare("UPDATE users SET luckyNum=? WHERE uid=?");

          $stmt->bind_param("is", $number, $uid);

          if ($stmt->execute()) {
               $stmt->close();
               return ['code' => 101, 'message' => "Entered Lucky Draw with " . $number];
          }

          $stmt->close();
          return ['code' => 102, 'message' => 'SPIN ALREADY CLAIMED.'];

     }

     public function addPointsAdds($uid)
     {
          $stmt = $this->con->prepare("UPDATE users SET points=points+30 WHERE uid=?");
          $stmt->bind_param("s", $uid);
          return ($stmt->execute());

     }

     public function addPointsAdd20($uid)
     {
          $stmt = $this->con->prepare("UPDATE users SET adsWatched=adsWatched+1,totalAdsWatched = totalAdsWatched + 1,points=points+20 WHERE uid=? AND adsWatched<=20");
          $stmt->bind_param("s", $uid);
          $stmt->execute();
          $stmt->close();

          $stmt1 = $this->con->prepare("SELECT tasks FROM users WHERE uid = ? AND adsWatched > 19");
          $stmt1->bind_param("s", $uid);
          $stmt1->execute();
          $stmt1->store_result();
          if ($stmt1->num_rows > 0) {
               $this->claimTask($uid, "5");
          }
          $stmt1->close();
          return 1;
     }

     public function dailyReset()
     {
          $stmt = $this->con->prepare("UPDATE users SET streak=streak+1, dailyAds=0, referralToday=0, spinCount=0, adsWatched=0, totalWatched = 0, totalRequests = totalRequests + requests, requests=0, videoWatched=0, req = lastRequest WHERE 1;");
          if ($stmt->execute()) {
               $stmt->close();
               $stmt1 = $this->con->prepare("UPDATE admin SET dailyReset=? WHERE id=1");
               $t = time();
               $stmt1->bind_param("i", $t);
               $stmt1->execute();
               $stmt1->close();

               $stmt = $this->con->prepare("UPDATE users SET streak=0,streakClaimed=-1 WHERE streak=7;");
               $stmt->execute();
               $stmt->close();

               $stmt = $this->con->prepare("UPDATE users SET streak=0,streakClaimed=-1 WHERE (streak-streakClaimed)>=2;");
               $stmt->execute();
               $stmt->close();

               $reset = $this->taskReset();
               return $reset;
          }

          return 2;
     }

     public function taskReset()
     {
          $conn = $this->con;
          $reset = 0;

          $stmtUser = $conn->prepare("SELECT uid, tasks FROM users WHERE 1 AND req != lastRequest");
          $stmtUser->execute();

          $taskNumbers = ["1", "2", "3", "4", "5", "6", "9", "10"];

          $result = $stmtUser->get_result();
          while ($row = $result->fetch_assoc()) {
               $uTask = json_decode($row['tasks'], true);
               foreach ($taskNumbers as $taskNumber) {
                    $uTask[$taskNumber]['claimed'] = false;
               }
               // Build the SQL update statement
               $stmt = $conn->prepare("UPDATE users SET tasks=? WHERE uid=?");
               $fin = json_encode($uTask);
               $stmt->bind_param("ss", $fin, $row['uid']);
               $stmt->execute();
               $stmt->close();
               $reset = $reset + 1;
          }
          $stmtUser->close();
          // Close the database connection
          $conn->close();
          return $reset;
     }

     public function luckyNumber()
     {
          $conn = $this->con;

          $luckyNumber = mt_rand(1, 9); // Adjust the range as needed

          $stmt = $conn->prepare("UPDATE users SET points=points+500, luckyNumTotal = luckyNumTotal + 1 WHERE luckyNum=?");
          $stmt->bind_param("i", $luckyNumber);
          $stmt->execute();
          $stmt->close();

          $t = time();

          $stmt = $conn->prepare("UPDATE users SET luckyNum=0 WHERE luckyNum != 0;");
          $stmt->execute();
          $stmt->close();

          $stmt = $conn->prepare("UPDATE admin SET luckyNumber=?,number=? WHERE id = 1");
          $stmt->bind_param("ii", $t, $luckyNumber);
          $stmt->execute();
          $stmt->close();

          return $luckyNumber;
     }

     public function terimakichu()
     {
          $stmtUser = $this->con->prepare("TRUNCATE TABLE requests");
          $stmtUser->execute();
          $stmtUser->close();
          return 1;
     }

     public function checkPayout($uid, $amount)
     {
          $stmt = $this->con->prepare("SELECT `points` FROM users WHERE Points >= ? AND `UID` = ?");
          $stmt->bind_param("is", $amount, $uid);
          $stmt->execute();
          $stmt->store_result();
          return $stmt->num_rows > 0;
          // return true if everything is fine 
     }

     public function checkPayoutLock($uid, $amount, $method)
     {
          $stmt = $this->con->prepare("SELECT `points` FROM users WHERE payoutLock = 1 AND `UID` = ?");
          $stmt->bind_param("s", $uid);
          $stmt->execute();
          $stmt->store_result();
          if ($stmt->num_rows > 0) {
               // payout lock is true
               if ($method == "PayPal" && $amount == "0.04") {
                    return 2;
               }
          } else {
               return 1;
          }
     }

     function generateRandomString($length = 10)
     {
          $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
          $charactersLength = strlen($characters);
          $randomString = '';
          for ($i = 0; $i < $length; $i++) {
               $randomString .= $characters[random_int(0, $charactersLength - 1)];
          }
          return $randomString;
     }

     public function payout($method, $amount, $email, $country, $uid)
     {
          if ($this->checkPayout($uid, $amount * 50000)) {
               if ($this->checkPayoutLock($uid, $amount, $method) == 1) {

                    $insertStatement = "INSERT INTO payout (method, amt, email, country, uid, date) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $this->con->prepare($insertStatement);
                    $date = date("d-m-y", time());
                    $stmt->bind_param("ssssss", $method, $amount, $email, $country, $uid, $date);
                    $stmt->execute();
                    $stmt->close();

                    $updateStatement = "UPDATE users SET points=points-?, payoutLock=1 WHERE uid=?";
                    $stmt = $this->con->prepare($updateStatement);
                    $amt = $amount * 50000;
                    $stmt->bind_param("is", $amt, $uid);
                    $stmt->execute();
                    $stmt->close();

                    return ['code' => 101, 'message' => 'PAYOUT SUCCESSFUL ✅'];
               } else {
                    return ['code' => 103, 'message' => 'PLEASE WAIT A FEW DAYS BEFORE SENDING THIS AMOUNT AGAIN'];
               }

          } else {
               return ['code' => 102, 'message' => 'INSUFFICIENT BALANCE'];
          }
     }
}

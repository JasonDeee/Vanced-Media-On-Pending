function doGet(e) {
  return HtmlService.createHtmlOutputFromFile("index");
}

function verifyCredential(credential) {
  try {
    const verification = OAuth2.verifyIdToken(credential, {
      audience:
        "153083226508-qmlv5ko26irv72rrshs6g2i3vnr4d1g4.apps.googleusercontent.com", // Thay bằng Client ID của bạn
    });

    const userEmail = verification.getEmail();

    // ... (logic sao chép spreadsheet ở đây)
    copySpreadsheetAndScripts(); // Gọi hàm sao chép sau khi xác thực thành công.

    return "Đăng nhập thành công với email: " + userEmail;
  } catch (e) {
    Logger.log("Invalid credential: " + e);
    return "Lỗi xác thực. " + e;
  }
}

function copySpreadsheetAndScripts() {
  // Liên kết đến file spreadsheet mẫu (đã được chia sẻ)
  const templateSpreadsheetUrl =
    "https://docs.google.com/spreadsheets/d/1LFpJvFOPiZeRExUUfEeOLw5HlJG2Hexzm86dQJBIY10/edit?usp=sharing"; // Thay bằng liên kết bạn đã sao chép ở bước 1

  try {
    // Lấy file spreadsheet mẫu từ URL
    const templateSpreadsheet = SpreadsheetApp.openByUrl(
      templateSpreadsheetUrl
    );

    // Lấy folder gốc trên Drive của người dùng đang chạy script
    const destinationFolder = DriveApp.getRootFolder();
    const newFolder = destinationFolder.createFolder("Vx_ChatBot"); // Tạo folder mới

    // Copy spreadsheet mẫu
    const newSpreadsheet = templateSpreadsheet.copy("Vx_ChatBot-Logger");
    DriveApp.getFileById(newSpreadsheet.getId()).moveTo(newFolder);

    // Lấy ID của spreadsheet mới được tạo
    const newSpreadsheetId = newSpreadsheet.getId();

    // Lấy script đính kèm của spreadsheet mẫu
    const templateScriptId = templateSpreadsheet.getBoundScriptId();

    if (templateScriptId) {
      // Copy script (Tạo version mới)
      const templateProject = ScriptApp.getProjectById(templateScriptId);
      const newScript = templateProject.createVersion("Copy of Script");

      // Di chuyển bản copy của script vào folder mới (Nếu cần thiết)
      const scriptFile = DriveApp.getFileById(newScript.getId());
      scriptFile.moveTo(newFolder);

      // Gán script mới cho spreadsheet mới được copy
      DriveApp.getFileById(newSpreadsheetId).setBoundScriptId(
        newScript.getId()
      );
    }

    Logger.log("Folder ID: " + newFolder.getId());
    Logger.log("Spreadsheet ID: " + newSpreadsheetId);
  } catch (error) {
    Logger.log("Lỗi khi copy: " + error);
    return "Đã xảy ra lỗi. Vui lòng thử lại."; // Trả về thông báo lỗi cho người dùng
  }

  return "Đã tạo folder và spreadsheet thành công!"; // Trả về thông báo thành công
}

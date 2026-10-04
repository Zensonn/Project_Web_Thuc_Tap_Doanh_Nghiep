# 8 lược đồ tuần tự use case ưu tiên

Từ 15 use case nghiệp vụ xác định theo vai trò và luồng PHP hiện có, tài liệu này chọn 8 use case trọng tâm để trình bày sequence diagram. Lựa chọn bao quát việc tạo và sử dụng tài khoản, cập nhật hồ sơ, tìm kiếm và ứng tuyển, xét duyệt đơn, nộp báo cáo và đánh giá báo cáo. Schema MySQL được dùng để đối chiếu dữ liệu vào/ra, không dùng mỗi bảng làm một use case. `accounts` là compatibility view của `users` theo schema trong `sql/phplogin.sql`.

Các sơ đồ bên dưới giữ mã UC gốc để đối chiếu với danh sách đầy đủ trong sơ đồ tổng quan. Use case được chọn: UC01 đăng ký tài khoản, UC02 đăng nhập, UC04 tìm kiếm vị trí, UC05 ứng tuyển, UC07 xét duyệt đơn, UC10 ghi nhật ký thực tập, UC11 nộp báo cáo và UC13 nhận xét báo cáo.

## Tổng quan use case

Use case được xác định từ mục tiêu của từng vai trò và các luồng ứng dụng; không ánh xạ một bảng CSDL thành một use case. CSDL giúp kiểm chứng dữ liệu nào được đọc/ghi và mối liên hệ giữa các nghiệp vụ.

![Sơ đồ use case đối chiếu với cơ sở dữ liệu](use-case-database-overview.svg)

| Nhóm use case | Bảng CSDL liên quan | Vai trò chính |
|---|---|---|
| Đăng ký, đăng nhập, phân quyền | `users` (`accounts` là compatibility view) | Khách, mọi vai trò |
| Quản lý hồ sơ | `users`, `students`, `companies`, `lecturers` | Sinh viên, doanh nghiệp, giảng viên |
| Tìm vị trí, ứng tuyển, xét duyệt | `internship_posts`, `applications`, `students`, `companies`, `lecturers` | Sinh viên, doanh nghiệp |
| Quản lý và theo dõi kỳ thực tập | `internships`, `applications`, `internship_posts` | Doanh nghiệp, sinh viên, giảng viên |
| Nhật ký thực tập | `internship_logs`, `internships`, `users` | Sinh viên, giảng viên xem theo dõi |
| Nộp báo cáo, nhận xét báo cáo | `reports`, `evaluations`, `internships`, `students`, `lecturers` | Sinh viên, giảng viên |
| Đánh giá sinh viên tại doanh nghiệp | `evaluations`, `internships`, `users` | Doanh nghiệp |
| Quản trị dữ liệu và thống kê | `users`, `majors`, `students`, `companies`, `lecturers`, `internship_posts`, `applications`, `internships`, `reports` | Quản trị viên |

Hai loại đánh giá được tách riêng: doanh nghiệp đánh giá sinh viên trên kỳ thực tập; giảng viên chấm và nhận xét báo cáo. Bảng `evaluations` dùng `evaluator_type` để phân biệt và có thể liên kết với `reports`.

Quy ước theo mẫu: mỗi sequence diagram có đúng một actor, một `boundary` chính (form giao diện `frm...`) và một `database` (`csdl`); `control` là thành phần xử lý. Các thao tác tệp được biểu diễn bên trong control để giữ sơ đồ gọn. Lời gọi nội bộ thể hiện bước kiểm tra như `checkPass()`, còn `alt/else` thể hiện nhánh điều kiện.

## Nhập vào Enterprise Architect

Tệp [sequence-diagrams-ea.xmi](sequence-diagrams-ea.xmi) hiện là gói UML XMI 2.1 gồm 10 Interaction tóm tắt, có khung `alt` chi tiết cho use case đăng nhập. Trong Enterprise Architect, mở project `.eap`/`.eapx`/`.qea`, chọn package đích trong Project Browser, rồi dùng **Publish > Model Exchange > Import Package from XMI** (tên menu có thể khác theo phiên bản) và chọn tệp XMI này. Các use case được đánh mã UC trong tài liệu và sơ đồ tổng quan; XMI chưa chứa đủ 15 luồng.

XMI là định dạng trao đổi mô hình, không phải project database `.eap` độc lập. Sơ đồ use case tổng quan và bảng đối chiếu schema ở trên là tài liệu riêng; tài liệu này trình bày chi tiết 8 use case ưu tiên, trong khi gói XMI hiện có 10 Interaction tóm tắt.

> Đăng ký hiện ghi `activation_code = "activated"`, do đó tài khoản có thể đăng nhập ngay; luồng email/kích hoạt riêng không nằm trong quy trình đăng ký hiện tại.

## Use case 01. Đăng ký tài khoản

Nguồn: `register.php`, `register-process.php`.

```mermaid
sequenceDiagram
    actor User as user
    boundary RegisterForm as frmDangKy
    control RegisterProcess as xuLyDangKy
    database DB as csdl

    User->>RegisterForm: nhapThongTin(username, email, matKhau, role)
    RegisterForm->>RegisterProcess: dangKy(thongTin)
    RegisterProcess->>RegisterProcess: validateInput()
    alt Thong tin khong hop le
        RegisterProcess-->>RegisterForm: baoLoi(thongBao)
        RegisterForm-->>User: hienThiLoi()
    else Thong tin hop le
        RegisterProcess->>DB: findUsername(username)
        DB-->>RegisterProcess: ketQuaTimKiem
        alt Username da ton tai
            RegisterProcess-->>RegisterForm: baoLoi("Username da ton tai")
            RegisterForm-->>User: hienThiLoi()
        else Username chua ton tai
            RegisterProcess->>RegisterProcess: hashPassword(matKhau)
            RegisterProcess->>DB: insertUser(activation_code="activated")
            DB-->>RegisterProcess: ketQuaLuu
            alt Tao tai khoan thanh cong
                RegisterProcess-->>RegisterForm: dangKyThanhCong()
                RegisterForm-->>User: hienThiThongBao()
            else Khong tao duoc tai khoan
                RegisterProcess-->>RegisterForm: baoLoi()
                RegisterForm-->>User: hienThiLoi()
            end
        end
    end
```
## Use case 02. Đăng nhập và điều hướng theo vai trò

Nguồn: `index.php`, `authenticate.php`.

```mermaid
sequenceDiagram
    actor User as user
    boundary LoginForm as frmDangNhap
    database DB as csdl

    User->>LoginForm: dangNhap(username, password)
    LoginForm->>DB: getUser(username)
    DB-->>LoginForm: user, passwordHash, status, activationCode, role
    alt [user khong ton tai]
        LoginForm-->>User: baoLoi("Sai ten dang nhap hoac mat khau")
    else [user ton tai]
        LoginForm->>LoginForm: checkStatusAndActivation()
        alt [tai khoan bi khoa hoac chua kich hoat]
            LoginForm-->>User: baoLoi(trangThaiTaiKhoan)
        else [tai khoan hoat dong va da kich hoat]
            LoginForm->>LoginForm: checkPass(password, passwordHash)
            alt [password khong khop]
                LoginForm-->>User: baoLoi("Sai ten dang nhap hoac mat khau")
            else [password khop]
                LoginForm->>LoginForm: taoSession(userId, username, role)
                LoginForm->>LoginForm: getRights(role)
                LoginForm-->>User: dieuHuongVaHienThiTrangChinhTheoVaiTro()
            end
        end
    end
```

## Use case 04. Tìm kiếm và xem chi tiết vị trí thực tập

Nguồn: `internships.php`, `internship-detail.php`.

```mermaid
sequenceDiagram
    actor Student as sinhVien
    boundary SearchForm as frmTimVaXemViTri
    control InternshipController as xuLyThucTap
    database DB as csdl

    Student->>SearchForm: timKiem(tuKhoa, diaDiem, hinhThuc)
    SearchForm->>InternshipController: searchInternships(filters)
    InternshipController->>InternshipController: checkStudentSession()
    InternshipController->>DB: findPublishedPosts(filters)
    DB-->>InternshipController: danhSachViTri
    InternshipController-->>SearchForm: hienThiDanhSach(danhSachViTri)
    SearchForm-->>Student: xemKetQua()
    Student->>SearchForm: chonViTri(postId)
    SearchForm->>InternshipController: getInternshipDetail(postId)
    InternshipController->>DB: getPostCompanyAndApplicationState(postId, userId)
    DB-->>InternshipController: chiTietViTri, trangThaiDon, cv, giangVien
    alt Vi tri khong ton tai hoac khong con published
        InternshipController-->>SearchForm: baoLoi("Khong tim thay vi tri")
        SearchForm-->>Student: hienThiLoi()
    else Tim thay vi tri
        InternshipController-->>SearchForm: chiTietViTriVaTrangThai
        SearchForm-->>Student: hienThiChiTietVaTuyChonUngTuyen()
    end
```

## Use case 05. Sinh viên nộp hoặc rút đơn ứng tuyển

Nguồn: `internship-detail.php`.

```mermaid
sequenceDiagram
    actor Student as sinhVien
    boundary ApplyForm as frmUngTuyen
    control ApplicationController as xuLyUngTuyen
    database DB as csdl

    Student->>ApplyForm: chonThaoTacUngTuyen()
    ApplyForm->>ApplicationController: handleApplicationAction(action, postId, formData)
    alt action la withdraw
        ApplicationController->>DB: findOwnApplication(postId, userId)
        DB-->>ApplicationController: applicationId, status
        alt Don dang submitted hoac reviewing
            ApplicationController->>DB: updateApplicationStatus(withdrawn)
            DB-->>ApplicationController: ketQuaCapNhat
            ApplicationController-->>ApplyForm: redirect(withdrawn=1)
            ApplyForm-->>Student: hienThiXacNhanRutDon()
        else Don khong ton tai hoac khong the rut
            ApplicationController-->>ApplyForm: baoLoiHoacKhongChoPhepRut()
            ApplyForm-->>Student: hienThiTrangThaiDon()
        end
    else action la apply
        ApplicationController->>DB: getStudentProfileAndCV(userId)
        DB-->>ApplicationController: studentProfile, cvFile
        ApplicationController->>DB: checkAcceptedInternship(studentId)
        DB-->>ApplicationController: acceptedInternshipState
        alt Chua cap nhat ho so sinh vien
            ApplicationController-->>ApplyForm: baoLoi("Can cap nhat ho so")
            ApplyForm-->>Student: hienThiLoi()
        else Sinh vien da duoc nhan thuc tap
            ApplicationController-->>ApplyForm: baoLoi("Khong the ung tuyen them")
            ApplyForm-->>Student: hienThiLoi()
        else Du dieu kien ung tuyen
            ApplicationController->>DB: checkLecturer(lecturerId)
            DB-->>ApplicationController: lecturerValid
            alt Giang vien khong hop le
                ApplicationController-->>ApplyForm: baoLoi("Hay chon giang vien huong dan")
                ApplyForm-->>Student: hienThiLoi()
            else Giang vien hop le
                ApplicationController->>DB: insertApplication(postId, studentId, lecturerId, coverLetter, cvFile)
                DB-->>ApplicationController: ketQuaLuu
                alt Luu don thanh cong
                    ApplicationController-->>ApplyForm: redirect(applied=1)
                    ApplyForm-->>Student: hienThiXacNhan()
                else Don bi trung hoac luu that bai
                    ApplicationController-->>ApplyForm: baoLoi()
                    ApplyForm-->>Student: hienThiLoi()
                end
            end
        end
    end
```

## Use case 07. Doanh nghiệp xét duyệt đơn ứng tuyển

Nguồn: `company/_common.php`, `company/applications.php`.

```mermaid
sequenceDiagram
    actor Company as doanhNghiep
    boundary ApplicationsForm as frmDanhSachUngTuyen
    control ApplicationController as xuLyDon
    database DB as csdl

    Company->>ApplicationsForm: moDanhSachUngTuyen()
    ApplicationsForm->>ApplicationController: getApplications()
    ApplicationController->>DB: findApplicationsByCompany(companyId)
    DB-->>ApplicationController: danhSachDon
    ApplicationController-->>ApplicationsForm: danhSachDon
    ApplicationsForm-->>Company: hienThiDonVaTrangThai()
    Company->>ApplicationsForm: chonTrangThai(applicationId, status)
    ApplicationsForm->>ApplicationController: updateApplication(applicationId, status)
    ApplicationController->>ApplicationController: validateStatusAndCompany()
    alt Don khong thuoc doanh nghiep hoac trang thai khong hop le
        ApplicationController-->>ApplicationsForm: baoLoiHoacBoQuaCapNhat()
        ApplicationsForm-->>Company: hienThiKetQua()
    else Don hop le va duoc chap nhan
        ApplicationController->>DB: updateApplicationStatus(accepted)
        DB-->>ApplicationController: capNhatThanhCong
        ApplicationController->>DB: createInternshipIfMissing(applicationId)
        DB-->>ApplicationController: taoKyThucTapPlanned
        ApplicationController-->>ApplicationsForm: capNhatThanhCong()
        ApplicationsForm-->>Company: hienThiTrangThaiMoi()
    else Don hop le, xem xet hoac tu choi
        ApplicationController->>DB: updateApplicationStatus(status)
        DB-->>ApplicationController: capNhatThanhCong
        ApplicationController-->>ApplicationsForm: capNhatThanhCong()
        ApplicationsForm-->>Company: hienThiTrangThaiMoi()
    end
```

## Use case 10. Sinh viên ghi nhật ký thực tập

Nguồn: `internship-logs.php`.

```mermaid
sequenceDiagram
    actor Student as sinhVien
    boundary LogForm as frmNhatKy
    control LogController as xuLyNhatKy
    database DB as csdl

    Student->>LogForm: moTrangNhatKy()
    LogForm->>LogController: getLogForm()
    LogController->>DB: findStudentInternships(userId)
    DB-->>LogController: danhSachKyThucTap
    LogController->>DB: findStudentLogs(studentId)
    DB-->>LogController: lichSuNhatKy
    LogController-->>LogForm: formVaLichSu
    LogForm-->>Student: hienThiFormVaLichSu()
    Student->>LogForm: nhapNgayNoiDungKetQua()
    LogForm->>LogController: saveLog(internshipId, logData)
    LogController->>LogController: validateRequiredFieldsAndOwnership()
    alt Thieu du lieu hoac khong co quyen
        LogController-->>LogForm: baoLoi()
        LogForm-->>Student: hienThiLoi()
    else Du lieu hop le
        LogController->>DB: insertInternshipLog(internshipId, logData)
        DB-->>LogController: ketQuaLuu
        alt Luu thanh cong
            LogController->>DB: findStudentLogs(studentId)
            DB-->>LogController: lichSuMoi
            LogController-->>LogForm: xacNhanVaLichSuMoi
            LogForm-->>Student: hienThiNhatKyMoi()
        else Luu that bai
            LogController-->>LogForm: baoLoi()
            LogForm-->>Student: hienThiLoi()
        end
    end
```

## Use case 11. Sinh viên nộp báo cáo thực tập

Nguồn: `student-portal.php`.

```mermaid
sequenceDiagram
    actor Student as sinhVien
    boundary ReportForm as frmNopBaoCao
    control ReportController as xuLyBaoCao
    database DB as csdl

    Student->>ReportForm: nhapBaoCaoVaChonFile()
    ReportForm->>ReportController: submitReport(reportData, file)
    ReportController->>ReportController: validateFieldsTypeAndFileSize()
    ReportController->>DB: checkInternshipOwnership(internshipId, studentId)
    DB-->>ReportController: internshipAllowed
    alt Du lieu khong hop le hoac ky thuc tap khong thuoc sinh vien
        ReportController-->>ReportForm: baoLoi()
        ReportForm-->>Student: hienThiLoi()
    else Du lieu hop le
        opt Co file bao cao
            ReportController->>ReportController: saveReportFile(randomFilename)
        end
        ReportController->>DB: insertReport(status="submitted", submittedAt=NOW)
        DB-->>ReportController: ketQuaLuu
        alt Luu bao cao thanh cong
            ReportController-->>ReportForm: xacNhanVaDanhSachBaoCao()
            ReportForm-->>Student: hienThiXacNhan()
        else Luu bao cao that bai
            opt Da luu file len he thong tep
                ReportController->>ReportController: deleteReportFile(fileUrl)
            end
            ReportController-->>ReportForm: baoLoi()
            ReportForm-->>Student: hienThiLoi()
        end
    end
```

## Use case 13. Giảng viên nhận xét báo cáo

Nguồn: `lecturer/_common.php`, `lecturer/reports.php`.

```mermaid
sequenceDiagram
    actor Lecturer as giangVien
    boundary ReviewForm as frmDanhGiaBaoCao
    control ReviewController as xuLyDanhGia
    database DB as csdl

    Lecturer->>ReviewForm: moDanhSachBaoCao()
    ReviewForm->>ReviewController: getAssignedReports()
    ReviewController->>DB: findReportsAssignedToLecturer(lecturerId)
    DB-->>ReviewController: danhSachBaoCao
    ReviewController-->>ReviewForm: danhSachBaoCao
    ReviewForm-->>Lecturer: hienThiBaoCao()
    Lecturer->>ReviewForm: nhapDiemVaNhanXet(reportId, score, comments)
    ReviewForm->>ReviewController: saveReview(reportId, score, comments)
    ReviewController->>ReviewController: validateScoreAndComments()
    ReviewController->>DB: checkReportAssignment(reportId, userId)
    DB-->>ReviewController: reportAllowed
    alt Du lieu khong hop le hoac khong co quyen
        ReviewController-->>ReviewForm: baoLoi()
        ReviewForm-->>Lecturer: hienThiLoi()
    else Duoc phep danh gia
        ReviewController->>DB: upsertEvaluation(evaluatorType="lecturer")
        DB-->>ReviewController: ketQuaLuu
        alt Luu danh gia thanh cong
            ReviewController->>DB: markReportReviewed(reportId, reviewedAt)
            DB-->>ReviewController: trangThaiReviewed
            ReviewController-->>ReviewForm: xacNhanDanhGia()
            ReviewForm-->>Lecturer: hienThiKetQuaDanhGia()
        else Luu danh gia that bai
            ReviewController-->>ReviewForm: baoLoi()
            ReviewForm-->>Lecturer: hienThiLoi()
        end
    end
```

<?php

namespace App\Repositories\Implements;

use App\prompts\BaseKnowledge;
use App\prompts\Prompts;
use App\Repositories\Interfaces\IGeminiRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Gemini\Data\GenerationConfig;
use Gemini\Data\SafetySetting;
use Gemini\Enums\HarmBlockThreshold;
use Gemini\Enums\HarmCategory;
use Gemini\Enums\ModelType;
use Gemini\Laravel\Facades\Gemini;
use Illuminate\Support\Facades\Log;

class GeminiRepository implements IGeminiRepository
{

    public Prompts $prompts;
    private $generativeModel;
    public function __construct(Prompts $prompts)
    {
        $this->prompts = $prompts;
        $safetySettingDangerousContent = new SafetySetting(
            category: HarmCategory::HARM_CATEGORY_DANGEROUS_CONTENT,
            threshold: HarmBlockThreshold::BLOCK_ONLY_HIGH
        );

        $safetySettingHateSpeech = new SafetySetting(
            category: HarmCategory::HARM_CATEGORY_HATE_SPEECH,
            threshold: HarmBlockThreshold::BLOCK_ONLY_HIGH
        );

        $generationConfig = new GenerationConfig(
            maxOutputTokens: 8192,
            temperature: 0.5,
            topP: 1,
            topK: 20,
        );

        $this->generativeModel = Gemini::generativeModel(ModelType::GEMINI_FLASH)
            ->withSafetySetting($safetySettingDangerousContent)
            ->withSafetySetting($safetySettingHateSpeech)
            ->withGenerationConfig($generationConfig);
    }

    public function getTopics($subject): array
    {
        $basePrompt = new BaseKnowledge();
        $subjectBase = $basePrompt->promptBaseSubject();
        try {
            // Tạo prompt cho mô hình
            $prompt = "
            Bạn là một hệ thống AI tạo danh sách JSON.
            Nếu là môn sinh học thì Hãy cung cấp danh sách tên 6 bài học bất kỳ của môn sinh học lớp 10 từ nội dung mục lục trong này: $subjectBase, dưới định dạng JSON duy nhất: [\"Chủ đề 1\", \"Chủ đề 2\"].
            Không thêm bất kỳ văn bản nào khác ngoài JSON.
            Nếu là môn $subject Hãy cung cấp danh sách khoảng 4 chủ đề của $subject ở Việt Nam đa dạng và phong phú, dưới định dạng JSON duy nhất: [\"Chủ đề 1\", \"Chủ đề 2\"].
            Không thêm bất kỳ văn bản nào khác ngoài JSON.

            Môn học là: $subject
            ";

            // Bắt đầu phiên trò chuyện
            $chatSession = $this->generativeModel->startChat();
            $response = $chatSession->sendMessage($prompt)->text(); // Lấy phản hồi từ mô hình

            // Chuẩn hóa phản hồi: Lọc phần JSON
            if (preg_match('/\[(.*?)\]/s', $response, $matches)) {
                $response = $matches[0]; // Lấy phần JSON chính xác
            } else {
                Log::warning("Không tìm thấy JSON hợp lệ trong phản hồi.", ['response' => $response]);
                return []; // Trả về giá trị mặc định
            }

            // Parse JSON từ phản hồi
            $topicsArray = json_decode($response, true);

            return $topicsArray;

        } catch (\Exception $e) {
            // Log lỗi chi tiết
            Log::error("Lỗi khi lấy danh sách chủ đề từ mô hình Gemini.", [
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    public function streamGenerateResponse($promptProccessRequest): string
    {
        $chatSession = $this->generativeModel->startChat();
        return $chatSession->sendMessage($promptProccessRequest)->text();
    }


    public function sendMessage(string $message): string
    {
        try {
            $chatSession = $this->generativeModel->startChat();

            $prompt = $this->prompts->responsePrompt($message);

            $response1 = $chatSession->sendMessage($prompt)->text();
            $prompt2 = "
                không bao gồm ký từ ``` và ký tự html.
                Yêu cầu của bạn là nâng cấp nội dung lên một phiên bản hoàn hảo hơn nữa, nội dung bổ sung thêm cụ thể hơn, cải tiến hơn, có thêm nhiều gợi ý thông minh hơn trong giáo án/ slide. Hãy tuân thủ các hướng dẫn sau đây để đảm bảo chất lượng:
                - highlight những nội dung cần highlight
                **1. Mục tiêu chính:**
                - Tạo ra nội dung sáng tạo hơn, chi tiết hơn nhưng vẫn đảm bảo tiêu chuẩn và tính ứng dụng thực tế.
                - Nội dung không chỉ rõ ràng mà còn hấp dẫn, có tính tương tác, và phù hợp với đối tượng người dùng.

                **2. Hướng dẫn chi tiết:**
                - Phân tích và cải thiện nội dung đã cung cấp trong **$response1**.
                - Sáng tạo thêm các ý tưởng mới hoặc bố cục để làm nổi bật nội dung quan trọng.
                - Bổ sung các ví dụ minh họa, hình ảnh, hoặc ý tưởng độc đáo để tăng tính hấp dẫn và dễ hiểu.

                **3. Tiêu chuẩn cần tuân thủ:**
                - Đảm bảo tính chuyên nghiệp: Nội dung cần được trình bày rõ ràng, mạch lạc và dễ hiểu.
                - Thiết kế trực quan: Kết hợp các yếu tố thẩm mỹ như màu sắc, font chữ, và bố cục hợp lý để thu hút sự chú ý.
                - Thông tin đầy đủ: Phân tích sâu sắc nhưng không quá tải, đảm bảo mỗi phần nội dung có trọng tâm rõ ràng.
                - Tuân thủ BaseKnowledge

                **4. Vai trò ( E-Master AI) của bạn là:**
                - Đóng vai trò là một trợ lý sáng tạo, cải tiến mọi mặt của nội dung trong **$response1** để tạo ra một phiên bản hoàn hảo hơn.
                - Đề xuất các cách cải thiện hoặc thay đổi bố cục để tối ưu hiệu quả trình bày.
                - Đề xuất và triển khai các ý tưởng cải tiến giao diện để tăng hiệu quả trình bày.

                Hãy tạo ra một phiên bản nội dung hoàn chỉnh hơn, tối ưu hơn và có giao diện rõ ràng, minh bạch, phù hợp với tiêu chuẩn đã đề ra cad kèm thêm hướng dẫn chi tiết và cụ thể cho từng nội dung trong giáo án này và highlight những nội dung cần highlight.
                **Dữ liệu gốc bạn cần tối ưu:**
                - $response1

                Hãy cung cấp phiên bản nội dung hoàn hảo hơn, sáng tạo hơn và đảm bảo đáp ứng đầy đủ tiêu chuẩn đã nêu trên.
            ";

            return $chatSession->sendMessage($prompt2)->text();
        } catch (\Exception $e) {
            return 'Lỗi khi gửi tin nhắn: ' . $e->getMessage();
        }
    }
//    private function generatePdf(string $htmlContent)
//    {
//        // Khởi tạo Dompdf
//        $options = new Options();
//        $options->set('defaultFont', 'Arial');
//        $dompdf = new Dompdf($options);
//
//        // Tải nội dung HTML vào Dompdf
//        $dompdf->loadHtml($htmlContent);
//
//        // (Tuỳ chọn) Thiết lập kích thước trang và chế độ dọc
//        $dompdf->setPaper('A4', 'portrait');
//
//        // Chuyển đổi HTML sang PDF
//        $dompdf->render();
//
//        // Xuất PDF ra trình duyệt để tải xuống
//        $dompdf->stream("fileLessonPlan.pdf", ["Attachment" => true]);
//    }
}

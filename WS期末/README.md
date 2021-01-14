# 電影電視劇門戶網站  
### 頁面結構說明：  
首頁（首頁）  
影視評論（詳情頁）  
登錄界面（表單界面）  
註冊界面（表單界面）  
站長介紹（列表頁）  
### 實作中遇到的困難：  
#### （1）首頁中的熱門電影動態切換效果  
該動態效果基於CSS3的transition與transform兩個屬性以及opacity這個屬性，技術難點在於對這幾個屬性的掌握程度  
##### transform 基本介紹 #####   
none 表示不進行變換；

rotate 旋轉； transform:rotate(20deg) 旋轉角度可以為負數。需要先有transform-origin定義旋轉的基點可為left top center right bottom 或坐標值（50px 70px）。

skew 扭曲 transform:skew(30deg,30deg) skew(相對x軸傾斜,相對y軸傾斜)

scale 縮放 transform:scale(2,3) 橫向放大2倍，縱向放大3倍。如等比放大寫壹個參數即可。  
##### transition 基本介紹 #####  
**W3C標準中對CSS3的transition這是樣描述的:“CSS的transition允許CSS的屬性值在壹定的時間區間內平滑地過渡。這種效果可以在鼠標單擊、獲得焦點、被點擊或對元素任何改變中觸發，並圓滑地以動畫效果改變CSS的屬性值。  
transition 主要包含四個屬性值：**  
transition-property： 執行變換的屬性；

transition- duration： 變換延續的時間：

transition-timing-function： 在延續時間段，變換的速率變化；

transition- delay ：變換延遲時間  
##### opacity 基本介紹 #####  
opacity是透明度的意思，通過可以設置元素的透明度。比如說壹個元素的opacity屬性設置為opacity(0.3)，那該元素透明度為70%。opacity(1)代表不透明。  
#### (2)解決方法： ####  
**初始**  
圖片的div(class="video")的opacity(1)(可無)，動態變換後的div(class="mask")的opacity(0),transform:scale(0.3);  
**.video:hover後**  
圖片Div的transform:scale(0.3);opacity(0);  
.mask{transform:scale(1);opacity(1);}  
**關鍵代碼：**  
```
.mask{
	transform: scale(0.3);
	transition: all 0.5s ease;
	filter: alpha(opacity=0);
 	opacity: 0;
 }
.video img{
 transform: scaleY(1);
 transition: all 0.7s ease-in-out;
}
.video:hover img{
  transform: scale(0.1);
  filter: alpha(opacity=0);
  opacity: 0;
 }
.video:hover .mask{
  transform: scale(1);
  filter: alpha(opacity=100);
  opacity: 1;
 }
 ```

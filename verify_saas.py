import os
from playwright.sync_api import sync_playwright

def run_saas_journey(page):
    # Ensure directories exist
    os.makedirs("/home/jules/verification/screenshots", exist_ok=True)

    # 1. Go to register page
    print("Navigating to register page...")
    page.goto("http://127.0.0.1:8000/register")
    page.wait_for_timeout(1000)

    # 2. Fill in Org details
    print("Filling organization details...")
    page.fill("#org_name", "Jules Susu Enterprise")
    page.wait_for_timeout(500)

    page.fill("#org_slug", "jules-susu")
    page.wait_for_timeout(500)

    # 3. Fill in Admin details
    print("Filling administrator details...")
    page.fill("#name", "Jules Admin")
    page.wait_for_timeout(500)

    page.fill("#email", "jules@susu.com")
    page.wait_for_timeout(500)

    page.fill("#phone", "0559998887")
    page.wait_for_timeout(500)

    page.fill("#phoneOne", "0241234567")
    page.wait_for_timeout(500)

    page.fill("#password", "Password123")
    page.wait_for_timeout(500)

    page.fill("#password_confirmation", "Password123")
    page.wait_for_timeout(1000)

    # Take screenshot of the filled registration page
    print("Taking registration page screenshot...")
    page.screenshot(path="/home/jules/verification/screenshots/registration_page.png")

    # 4. Submit form
    print("Submitting the form...")
    page.click("button[type='submit']")
    page.wait_for_timeout(3000) # Wait for redirect and render

    # 5. Take screenshot of dashboard showing new tenant branding
    print("Taking dashboard page screenshot...")
    page.screenshot(path="/home/jules/verification/screenshots/dashboard_page.png")
    page.wait_for_timeout(1000)

if __name__ == "__main__":
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        context = browser.new_context(
            record_video_dir="/home/jules/verification/videos"
        )
        page = context.new_page()
        try:
            run_saas_journey(page)
        finally:
            context.close()
            browser.close()
    print("Verification script finished successfully!")
